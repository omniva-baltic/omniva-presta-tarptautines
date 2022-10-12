<?php

// ALTER TABLE `ps_omniva_int_parcel` ADD COLUMN `shipment_id` varchar(100) DEFAULT NULL;
// ALTER TABLE `ps_omniva_int_parcel` ADD COLUMN `cart_id` varchar(100) DEFAULT NULL;
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

    public $shipment_id;

    public $cart_id;

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
            'shipment_id' =>         ['type' => self::TYPE_STRING, 'size' => 100],
            'cart_id' =>             ['type' => self::TYPE_STRING, 'size' => 100],
        ],
        'associations' => [
            'shipment' => ['type' => self::HAS_ONE],
        ],
    ];

    public static function getParcelsByOrderId($id_order)
    {
        $query = (new DbQuery())
            ->select("*")
            ->from(self::$definition['table'])
            ->where('id_order = ' . $id_order);

        return Db::getInstance()->executeS($query);
    }

    public static function getCountUntrackedParcelsByOrderId($id_order)
    {
        $query = (new DbQuery())
            ->select("COUNT(id)")
            ->from(self::$definition['table'])
            ->where('id_order = ' . $id_order . ' AND (tracking_number IS NULL OR tracking_number = "")');

        return (int) Db::getInstance()->getValue($query);
    }


    public static function getTrackingNumbersByOrderId($id_order)
    {
        $query = (new DbQuery())
            ->select("tracking_number")
            ->from(self::$definition['table'])
            ->where('id_order = ' . $id_order . ' AND tracking_number IS NOT NULL AND tracking_number != ""');

        return array_map(function($parcel) {
                return $parcel['tracking_number'];
        }, Db::getInstance()->executeS($query));
    }

}
