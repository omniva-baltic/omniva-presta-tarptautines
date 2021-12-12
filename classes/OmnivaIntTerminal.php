<?php

class OmnivaIntTerminal extends ObjectModel
{
    public $id;

    public $code;

    public $zip;

    public $address;

    public $city;

    public $weight_limit;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'omniva_int_terminal',
        'primary' => 'id',
        'fields' => [
                'code' =>             ['type' => self::TYPE_STRING, 'size' => 50],
                'zip' =>              ['type' => self::TYPE_STRING, 'size' => 10],
                'address' =>          ['type' => self::TYPE_STRING, 'size' => 255],
                'city' =>             ['type' => self::TYPE_STRING, 'size' => 50],
                'weight_limit' =>     ['type' => self::TYPE_INT, 'size' => 10],
            ],
        ];

}
