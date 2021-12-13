<?php

class OmnivaIntTerminal extends ObjectModel
{
    public $id;

    public $name;

    public $city;

    public $country_code;

    public $address;

    public $zipcode;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'omniva_int_terminal',
        'primary' => 'id',
        'fields' => [
                'name' =>           ['type' => self::TYPE_STRING, 'size' => 255],
                'city' =>           ['type' => self::TYPE_STRING, 'size' => 100],
                'country_code' =>   ['type' => self::TYPE_STRING, 'size' => 3],
                'address' =>        ['type' => self::TYPE_STRING, 'size' => 255],
                'zipcode' =>        ['type' => self::TYPE_STRING, 'size' => 10],
            ],
        ];

}
