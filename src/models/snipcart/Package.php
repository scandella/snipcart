<?php
/**
 * Snipcart plugin for Craft CMS 3.x
 *
 * @link      https://fostercommerce.com
 * @copyright Copyright (c) 2018 Working Concept Inc.
 */

namespace fostercommerce\snipcart\models\snipcart;

use craft\base\Model;
class Package extends Model
{
    public const WEIGHT_UNIT_GRAM = 'gram';

    public const WEIGHT_UNIT_POUND = 'pound';

    public const WEIGHT_UNIT_OUNCE = 'ounce';

    public const DIMENSION_UNIT_INCH = 'inch';

    public const DIMENSION_UNIT_CENTIMETER = 'centimeter';

    /**
     * @var string Friendly slug for this packaging type.
     */
    public $name;

    /**
     * @var int Length of package.
     */
    public $length;

    /**
     * @var int Width of package.
     */
    public $width;

    /**
     * @var int Height of package.
     */
    public $height;

    /**
     * @var int Package weight in grams.
     */
    public $weight;

    /**
     * @var string Unit of weight measurement. (Snipcart uses grams.)
     */
    public $weightUnit = self::WEIGHT_UNIT_GRAM;

    /**
     * @var string Unit of dimension measurements.
     */
    public $dimensionUnit = self::DIMENSION_UNIT_INCH;

    public function rules(): array
    {
        return [
            [['length', 'width', 'height', 'weight'],
                'number',
                'integerOnly' => false,
            ],
            [['name'], 'string'],
            [['name', 'length', 'width', 'height', 'weight'], 'required'],
            [['weightUnit'],
                'in',
                'range' => [self::WEIGHT_UNIT_GRAM, self::WEIGHT_UNIT_POUND, self::WEIGHT_UNIT_OUNCE],
            ],
            [['dimensionUnit'],
                'in',
                'range' => [self::DIMENSION_UNIT_INCH, self::DIMENSION_UNIT_CENTIMETER],
            ],
        ];
    }

    /**
     * True if valid, non-zero length, width, and height are all present.
     */
    public function hasPhysicalDimensions(): bool
    {
        return $this->length !== null && $this->length > 0 &&
            $this->width !== null && $this->width > 0 &&
            $this->height !== null && $this->height > 0;
    }
}
