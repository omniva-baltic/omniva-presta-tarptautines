<?php

class OmnivaIntCategory extends ObjectModel
{
    public $id_category;

    public $hs_code;

    public $weight;

    public $length;

    public $width;

    public $height;

    public $active;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'omniva_int_category',
        'primary' => 'id_category',
        'fields' => [
                'hs_code' =>       ['type' => self::TYPE_STRING, 'size' => 255],
                'weight' =>        ['type' => self::TYPE_FLOAT, 'size' => 10],
                'length' =>        ['type' => self::TYPE_FLOAT, 'size' => 10],
                'width' =>         ['type' => self::TYPE_FLOAT, 'size' => 10],
                'height' =>        ['type' => self::TYPE_FLOAT, 'size' => 10],
                'active' =>        ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
        ],
    ];

}
