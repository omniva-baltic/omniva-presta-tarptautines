<?php

class OmnivaIntCarrier extends ObjectModel
{
    public $id;

    public $id_reference;

    public $price_type;

    public $price;

    public $free_shipping;

    public $cheapest;

    public $type;

    public $radius;

    public $active = 1;

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
                'id_reference' =>     ['type' => self::TYPE_INT, 'required' => true, 'size' => 10],
                'price_type' =>       ['type' => self::TYPE_STRING, 'required' => true, 'size' => 30],
                'price' =>            ['type' => self::TYPE_FLOAT, 'required' => true, 'size' => 10, 'validate' => 'isPrice'],
                'free_shipping' =>    ['type' => self::TYPE_FLOAT, 'required' => true, 'size' => 10, 'validate' => 'isPrice'],
                'cheapest' =>         ['type' => self::TYPE_BOOL, 'required' => true, 'validate' => 'isBool'],
                'type' =>             ['type' => self::TYPE_STRING, 'required' => true, 'size' => 30],
                'radius' =>           ['type' => self::TYPE_INT, 'required' => true, 'size' => 10],
                'active' =>           ['type' => self::TYPE_BOOL, 'required' => true, 'validate' => 'isBool'],
                'date_add' =>         ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
                'date_upd' =>         ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            ],
        ];


    /**
     * Get carrier using the reference id.
     */
    public static function getCarrierByReference($id_reference)
    {

        $query = (new DbQuery())
            ->select("id")
            ->from(self::$definition['table'])
            ->where('id_reference = ' . $id_reference);

        $id_carrier = Db::getInstance()->getValue($query);
        if (!$id_carrier) {
            return false;
        }

        return new OmnivaIntCarrier($id_carrier);
    }

    public static function getCarriersIds()
    {
        $query = (new DbQuery())
            ->select("id")
            ->from(self::$definition['table']);

        return Db::getInstance()->executeS($query);
    }

}
