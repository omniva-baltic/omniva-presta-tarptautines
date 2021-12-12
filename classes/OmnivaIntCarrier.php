<?php

class OmnivaIntCarrier extends ObjectModel
{
    public $id;

    public $id_carrier;

    public $id_service;

    public $price_type;

    public $price;

    public $free_shipping;

    public $select_fastest;

    public $user_login;

    /** @var string Object creation date */
    public $date_add;

    /** @var string Object last modification date */
    public $date_upd;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'omniva_int_carrier',
        'primary' => 'id',
        'fields' => [
                'id_carrier' =>       ['type' => self::TYPE_INT, 'required' => true, 'size' => 10],
                'id_service' =>       ['type' => self::TYPE_INT, 'required' => true, 'size' => 10],
                'price_type' =>       ['type' => self::TYPE_STRING, size => 15],
                'price' =>            ['type' => self::TYPE_FLOAT, size => 10, 'validate' => 'isPrice'],
                'free_shipping' =>    ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
                'select_fastest' =>   ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
                'user_login' =>       ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
                'date_add' =>         ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
                'date_upd' =>         ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            ],
        ];

}
