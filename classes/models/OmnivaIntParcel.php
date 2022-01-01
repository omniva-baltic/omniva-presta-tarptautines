<?php

class OmnivaIntParcel extends ObjectModel
{
    public $id;

    public $id_shipment;

    public $amount;

    public $weight;

    public $length;

    public $width;

    public $height;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'omniva_int_parcel',
        'primary' => 'id',
        'fields' => [
            'id_shipment' =>   ['type' => self::TYPE_INT, 'required' => true, 'size' => 10],
            'amount' =>        ['type' => self::TYPE_INT, 'size' => 10],
            'weight' =>        ['type' => self::TYPE_FLOAT, 'size' => 10],
            'length' =>        ['type' => self::TYPE_FLOAT, 'size' => 10],
            'width' =>         ['type' => self::TYPE_FLOAT, 'size' => 10],
            'height' =>        ['type' => self::TYPE_FLOAT, 'size' => 10],
        ],
        'associations' => [
            'shipment' => ['type' => self::HAS_ONE],
        ],
    ];

}
