<?php
/**
 * Snipcart plugin for Craft CMS 3.x
 *
 * @link      https://fostercommerce.com
 * @copyright Copyright (c) 2018 Working Concept Inc.
 */

namespace fostercommerce\snipcart\models\snipcart;

use craft\base\Model;
class CustomField extends Model
{
    /**
     * @var
     */
    public $name;

    /**
     * @var
     */
    public $operation;

    /**
     * @var
     */
    public $type;

    /**
     * @var
     */
    public $options;

    /**
     * @var
     */
    public $required;

    /**
     * @var
     */
    public $value;

    /**
     * @var
     */
    public $optionsArray;
}
