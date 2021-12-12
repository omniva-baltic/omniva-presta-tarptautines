<?php

class OmnivaIntCategory extends ObjectModel
{
    public $id;

    public $id_category;

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
        'primary' => 'id',
        'fields' => [
                'id_category' =>   ['type' => self::TYPE_INT, 'size' => 10],
                'weight' =>        ['type' => self::TYPE_FLOAT, 'size' => 10],
                'length' =>        ['type' => self::TYPE_FLOAT, 'size' => 10],
                'width' =>         ['type' => self::TYPE_FLOAT, 'size' => 10],
                'height' =>        ['type' => self::TYPE_FLOAT, 'size' => 10],
                'active' =>        ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            ],
        ];

}
