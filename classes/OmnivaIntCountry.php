<?php

class OmnivaIntCountry extends ObjectModel
{
    public $id;

    public $name;

    public $en_name;

    public $code;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'omniva_int_country',
        'primary' => 'id_country',
        'fields' => [
                'name' =>           ['type' => self::TYPE_STRING, 'size' => 255],
                'en_name' =>        ['type' => self::TYPE_STRING, 'size' => 255],
                'code' =>           ['type' => self::TYPE_STRING, 'size' => 3],
            ],
        ];
}