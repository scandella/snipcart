<?php
/**
 * Snipcart plugin for Craft CMS 3.x
 *
 * @link      https://workingconcept.com
 * @copyright Copyright (c) 2018 Working Concept Inc.
 */

namespace workingconcept\snipcart\models;

class SnipcartCustomerStatistics extends \craft\base\Model
{

    // Constants
    // =========================================================================


    // Properties
    // =========================================================================

    /**
     * @var int
     */
    public $ordersCount;

    /**
     * @var float
     */
    public $ordersAmount;


    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['ordersCount'], 'number', 'integerOnly' => true],
            [['ordersAmount'], 'number', 'integerOnly' => false],
            [['ordersCount', 'ordersAmount'], 'default', 'value' => 0],
        ];
    }
}
