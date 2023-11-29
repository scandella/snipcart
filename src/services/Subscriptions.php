<?php
/**
 * Snipcart plugin for Craft CMS 3.x
 *
 * @link      https://fostercommerce.com
 * @copyright Copyright (c) 2018 Working Concept Inc.
 */

namespace fostercommerce\snipcart\services;

use craft\base\Component;
use fostercommerce\snipcart\helpers\ModelHelper;
use fostercommerce\snipcart\models\snipcart\Subscription;
use fostercommerce\snipcart\Snipcart;

/**
 * Class Subscriptions
 *
 * For interacting with Snipcart subscriptions.
 *
 * @package fostercommerce\snipcart\services
 */
class Subscriptions extends Component
{
    /**
     * Lists subscriptions.
     *
     * @param int    $page   Page of results
     * @param int    $limit  Number of results per page
     * @param array  $params Parameters to send with the request
     *
     * @return \stdClass
     *              ->items (Subscription[])
     *              ->totalItems (int)
     *              ->offset (int)
     *              ->limit (int)
     * @throws \Exception when there isn't an API key to authenticate requests.
     */
    public function listSubscriptions($page = 1, $limit = 20, array $params = []): \stdClass
    {
        /**
         * Define offset and limit since that's pretty much all we're doing here.
         */
        $params['offset'] = ($page - 1) * $limit;
        $params['limit'] = $limit;

        $response = Snipcart::$plugin->api->get(
            'subscriptions',
            $params
        );

        return (object) [
            'items' => ModelHelper::safePopulateArrayWithModels(
                $response->items,
                Subscription::class
            ),
            'totalItems' => $response->totalItems,
            'offset' => $response->offset,
            'limit' => $limit,
        ];
    }

    /**
     * Gets a Snipcart subscription.
     *
     * @param string $subscriptionId Snipcart subscription ID
     *
     * @return Subscription|null
     * @throws \Exception if our API key is missing.
     */
    public function getSubscription($subscriptionId)
    {
        if ($subscriptionData = Snipcart::$plugin->api->get(sprintf(
            'subscriptions/%s',
            $subscriptionId
        ))) {
            return ModelHelper::safePopulateModel(
                (array) $subscriptionData,
                Subscription::class
            );
        }

        return null;
    }

    /**
     * Returns invoices related to a subscription.
     *
     * @param $subscriptionId
     *
     * @throws \Exception
     */
    public function getSubscriptionInvoices($subscriptionId): array
    {
        $response = Snipcart::$plugin->api->get(sprintf(
            'subscriptions/%s/invoices',
            $subscriptionId
        ));

        return is_array($response) ? $response : [];
    }

    /**
     * Cancels a subscription.
     *
     * @param string $subscriptionId Snipcart subscription ID
     *
     * @return mixed
     * @throws \Exception if our API key is missing.
     */
    public function cancel($subscriptionId)
    {
        return Snipcart::$plugin->api->delete(
            sprintf('subscriptions/%s', $subscriptionId)
        );
    }

    /**
     * Pauses a subscription.
     *
     * @param string $subscriptionId Snipcart subscription ID
     *
     * @return mixed
     * @throws \Exception if our API key is missing.
     */
    public function pause($subscriptionId)
    {
        return Snipcart::$plugin->api->post(
            sprintf('subscriptions/%s/pause', $subscriptionId)
        );
    }

    /**
     * Resumes a subscription.
     *
     * @param string $subscriptionId Snipcart subscription ID
     *
     * @return mixed
     * @throws \Exception if our API key is missing.
     */
    public function resume($subscriptionId)
    {
        return Snipcart::$plugin->api->post(
            sprintf('subscriptions/%s/resume', $subscriptionId)
        );
    }
}
