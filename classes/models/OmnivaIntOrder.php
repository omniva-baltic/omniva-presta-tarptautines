<?php

class OmnivaIntOrder extends ObjectModel
{
    public $id;

    public $id_shop;

    public $service_code;

    public $shipment_id;

    public $cart_id;

    public $cod;

    public $cod_amount;

    public $insurance;

    public $carry_service;

    public $doc_return;

    public $own_login;

    public $fragile;

    /** @var string Object creation date */
    public $date_add;

    /** @var string Object last modification date */
    public $date_upd;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'omniva_int_order',
        'primary' => 'id',
        'fields' => [
            'id_shop' =>             ['type' => self::TYPE_INT, 'required' => true, 'size' => 10],
            'service_code' =>        ['type' => self::TYPE_STRING, 'size' => 20],
            'shipment_id' =>         ['type' => self::TYPE_STRING, 'size' => 100],
            'cart_id' =>             ['type' => self::TYPE_STRING, 'size' => 100],
            'cod' =>                 ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'cod_amount' =>          ['type' => self::TYPE_FLOAT, 'size' => 10],
            'insurance' =>           ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'carry_service' =>       ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'doc_return' =>          ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'own_login' =>           ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'fragile' =>             ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'date_add' =>            ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'date_upd' =>            ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            ],
        ];

}
