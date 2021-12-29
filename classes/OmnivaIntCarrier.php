<?php

class OmnivaIntCarrier extends ObjectModel
{
    public $id;

    public $id_carrier;

    public $price_type;

    public $price;

    public $free_shipping;

    public $my_login;

    public $user;

    public $password;

    public $cheapest;

    public $radius;

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
                'price_type' =>       ['type' => self::TYPE_STRING, 'required' => true, 'size' => 30],
                'price' =>            ['type' => self::TYPE_FLOAT, 'required' => true, 'size' => 10, 'validate' => 'isPrice'],
                'free_shipping' =>    ['type' => self::TYPE_FLOAT, 'required' => true, 'size' => 10, 'validate' => 'isPrice'],
                'my_login' =>         ['type' => self::TYPE_BOOL, 'required' => true, 'validate' => 'isBool'],
                'user' =>             ['type' => self::TYPE_STRING, 'size' => 50],
                'password' =>         ['type' => self::TYPE_STRING, 'size' => 50],
                'cheapest' =>         ['type' => self::TYPE_BOOL, 'required' => true, 'validate' => 'isBool'],
                'radius' =>           ['type' => self::TYPE_INT, 'required' => true, 'size' => 10],
                'date_add' =>         ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
                'date_upd' =>         ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            ],
        ];

}
