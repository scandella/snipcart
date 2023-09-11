<?php
/**
 * Snipcart plugin for Craft CMS 3.x
 *
 * @link      https://fostercommerce.com
 * @copyright Copyright (c) 2018 Working Concept Inc.
 */

namespace fostercommerce\snipcart\services;

use craft\base\Component;
use yii\base\Exception;
use Craft;
use craft\mail\Message;
use fostercommerce\snipcart\helpers\VersionHelper;
use fostercommerce\snipcart\Snipcart;
use Pelago\Emogrifier\CssInliner;

/**
 * Sends notifications as things happen. Currently just email.
 *
 * @package fostercommerce\snipcart\services
 * @todo Make a proper Notification model
 */
class Notifications extends Component
{
    /**
     * @var string Path to Twig template for HTML email.
     */
    private $htmlEmailTemplate;

    /**
     * @var string Path to Twig template for plain text email.
     */
    private $textEmailTemplate;

    /**
     * @var bool Whether supplied email template paths are provided by the site
     *           (frontend) or the control panel. Important for switching the
     *           view's template mode when rendering HTML.
     */
    private $emailTemplatesAreFrontEnd;

    /**
     * @var string Slack webhook URL for notifications. (Not implemented.)
     */
    private $slackWebhook;

    /**
     * @var mixed Variables that should be fed to the Twig notification template.
     */
    private $notificationVars;

    /**
     * @var array Error strings accumulated during notification setup+attempt.
     */
    private array $errors = [];

    /**
     * Sets template variables for the notification.
     * Should be called before `sendEmail()`.
     */
    public function setNotificationVars(mixed $data): void
    {
        $this->notificationVars = $data;
    }

    /**
     * Gets template variables for the notification.
     *
     * @return mixed
     */
    public function getNotificationVars()
    {
        return $this->notificationVars;
    }

    /**
     * Sets notification errors.
     *
     * @param $errors
     */
    public function setErrors(array $errors): void
    {
        $this->errors = $errors;
    }

    /**
     * Sets notification email template.
     *
     * @param string $htmlTemplate Twig template path to be used for the HTML
     *                             email notification
     * @param string $textTemplate Twig template path to be used for an
     *                             alternate, plain text email notification
     * @param bool   $frontend     Whether the supplied path is on the front
     *                             (site) end or the back (plugin) end, which
     *                             matters for setting the template mode.
     */
    public function setEmailTemplate($htmlTemplate, $textTemplate = null, $frontend = false): void
    {
        $this->htmlEmailTemplate = $htmlTemplate;
        $this->textEmailTemplate = $textTemplate;
        $this->emailTemplatesAreFrontEnd = $frontend;
    }

    /**
     * Sets the Slack webhook URL.
     *
     * @param string $url
     */
    public function setSlackWebhook($url): void
    {
        $this->slackWebhook = $url;
    }

    /**
     * Sends an email notification.
     *
     * @param array  $to       Array of email addresses
     * @param string $subject  Email subject
     *
     * @return bool `true` on success, `false` otherwise (see ->getErrors())
     * @throws
     */
    public function sendEmail($to, $subject): bool
    {
        $model = Snipcart::$plugin->getSettings();

        // Bail if we’re in test mode and don’t want to send emails.
        if ($model->testMode && ! $model->sendTestModeEmail) {
            // pretend it went well
            return true;
        }

        $errors = [];
        $view = Craft::$app->getView();

        if (VersionHelper::isCraft31()) {
            $emailSettings = Craft::$app->getProjectConfig()->get('email');
        } else {
            $emailSettings = Craft::$app->getSystemSettings()->getEmailSettings();
        }

        /**
         * Switch template mode only if we need to rely on our own template.
         */
        if ($this->emailTemplatesAreFrontEnd === false) {
            /**
             * Remember what we started with.
             */
            $originalTemplateMode = $view->getTemplateMode();

            /**
             * Explicitly set to CP mode.
             *
             * This could technically throw an exception over an invalid
             * template mode, but we're not worried.
             */
            $view->setTemplateMode($view::TEMPLATE_MODE_CP);
        }

        /**
         * Make sure we've got valid templates now that we've
         * switched the view mode.
         */
        if (! $this->ensureTemplatesExist()) {
            $this->setErrors(['Email template(s) are missing.']);
            return false;
        }

        // render the HTML message
        $messageHtml = $view->renderPageTemplate(
            $this->htmlEmailTemplate,
            $this->getNotificationVars()
        );

        // inline the message’s styles so they're more likely to be applied
        $mergedHtml = CssInliner::fromHtml($messageHtml)->inlineCss()->render();

        $messageText = '';

        if ($this->textEmailTemplate !== '' && $this->textEmailTemplate !== '0') {
            $messageText = $view->renderPageTemplate(
                $this->textEmailTemplate,
                $this->getNotificationVars()
            );
        }

        foreach ($to as $address) {
            $message = new Message();

            $message->setFrom([
                $emailSettings['fromEmail'] => $emailSettings['fromName'],
            ]);

            $message->setTo($address);
            $message->setSubject($subject);
            $message->setHtmlBody($mergedHtml);

            if ($messageText) {
                $message->setTextBody($messageText);
            }

            if (! Craft::$app->getMailer()->send($message)) {
                $problem = sprintf('Notification failed to send to %s!', $address);
                Craft::warning($problem, 'snipcart');
                $errors[] = $problem;
            }
        }

        if ($this->emailTemplatesAreFrontEnd === false &&
            isset($originalTemplateMode)
        ) {
            $view->setTemplateMode($originalTemplateMode);
        }

        $this->setErrors($errors);

        return $errors === [];
    }

    /**
     * Makes sure that the HTML email template exists at the specified path,
     * and that the text template exists if it was provided.
     *
     * @throws Exception
     */
    private function ensureTemplatesExist(): bool
    {
        if (! $this->ensureTemplateExists($this->htmlEmailTemplate)) {
            return false;
        }

        if ($this->textEmailTemplate !== '' && $this->textEmailTemplate !== '0') {
            $this->ensureTemplateExists($this->textEmailTemplate);

            return false;
        }

        return true;
    }

    /**
     * Makes sure the template exists at the supplied path!
     *
     * @param $path
     *
     * @throws Exception
     */
    private function ensureTemplateExists($path): bool
    {
        if (! Craft::$app->getView()->doesTemplateExist($path)) {
            Craft::error(sprintf(
                'Specified email template `%s` does not exist.',
                $path

            ), 'snipcart');
            return false;
        }

        return true;
    }
}
