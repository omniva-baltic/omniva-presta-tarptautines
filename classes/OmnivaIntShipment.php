<?php

class OmnivaIntShipment extends ObjectModel
{
    public $id;

    public $id_cart;

    public $id_order;

    public $id_shop;

    public $id_terminal;

    public $id_manifest;

    public $shipment_weight;

    /** @var string Object creation date */
    public $date_add;

    /** @var string Object last modification date */
    public $date_upd;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'omniva_int_shipment',
        'primary' => 'id',
        'fields' => [
            'id_cart' =>             ['type' => self::TYPE_INT, 'required' => true, 'size' => 10],
            'id_order' =>            ['type' => self::TYPE_INT, 'size' => 10],
            'id_shop' =>             ['type' => self::TYPE_INT, 'required' => true, 'size' => 10],
            'id_terminal' =>         ['type' => self::TYPE_INT, 'size' => 10],
            'id_manifest' =>         ['type' => self::TYPE_INT, 'size' => 10],
            'shipment_weight' =>     ['type' => self::TYPE_FLOAT, 'size' => 10],
            'date_add' =>            ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'date_upd' =>            ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            ],
        ];

}
