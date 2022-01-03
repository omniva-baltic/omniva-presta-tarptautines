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

    public static function getParcelsByOrderId($id_order)
    {
        $query = (new DbQuery())
            ->select("id")
            ->from(self::$definition['table'])
            ->where('id_order = ' . $id_order);

        return array_map(function($parcel) {
                return $parcel['id'];
        }, Db::getInstance()->executeS($query));
    }

    public static function getCountUntrackedParcelsByOrderId($id_order)
    {
        $query = (new DbQuery())
            ->select("COUNT(id)")
            ->from(self::$definition['table'])
            ->where('id_order = ' . $id_order . ' AND tracking_number IS NULL OR tracking_number = ""');

        return (int) Db::getInstance()->getValue($query);
    }


    public static function getTrackingNumbersByOrderId($id_order)
    {
        $query = (new DbQuery())
            ->select("tracking_number")
            ->from(self::$definition['table'])
            ->where('id_order = ' . $id_order);

        return array_map(function($parcel) {
                return $parcel['tracking_number'];
        }, Db::getInstance()->executeS($query));
    }

}
