<?php

class OmnivaIntRateCache extends ObjectModel
{
    public $id;

    public $id_cart;

    public $hash;

    public $rate;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'omniva_int_rate_cache',
        'primary' => 'id',
        'fields' => [
            'id_cart' =>     ['type' => self::TYPE_INT, 'required' => true, 'size' => 10],
            'hash' =>        ['type' => self::TYPE_STRING, 'size' => 32],
            'rate' =>        ['type' => self::TYPE_FLOAT, 'size' => 10],
        ],
    ];

    public static function getCachedRate($id_cart, $hash)
    {
        $query = (new DbQuery())
            ->select("rate")
            ->from(self::$definition['table'])
            ->where('id_cart = ' . $id_cart)
            ->where("hash = '$hash'");

        return  Db::getInstance()->getValue($query);
    }
}