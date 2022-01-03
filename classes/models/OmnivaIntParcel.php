<?php

class OmnivaIntParcel extends ObjectModel
{
    public $id;

    public $id_order;

    public $amount;

    public $weight;

    public $length;

    public $width;

    public $height;

    public $tracking_number;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'omniva_int_parcel',
        'primary' => 'id',
        'fields' => [
            'id_order' =>           ['type' => self::TYPE_INT, 'required' => true, 'size' => 10],
            'amount' =>             ['type' => self::TYPE_INT, 'size' => 10],
            'weight' =>             ['type' => self::TYPE_FLOAT, 'size' => 10],
            'length' =>             ['type' => self::TYPE_FLOAT, 'size' => 10],
            'width' =>              ['type' => self::TYPE_FLOAT, 'size' => 10],
            'height' =>             ['type' => self::TYPE_FLOAT, 'size' => 10],
            'tracking_number' =>    ['type' => self::TYPE_STRING, 'size' => 100],
        ],
        'associations' => [
            'shipment' => ['type' => self::HAS_ONE],
        ],
    ];

}
