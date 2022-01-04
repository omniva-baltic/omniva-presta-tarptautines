<?php

class OmnivaIntManifest extends ObjectModel
{
    public $id;

    public $id_shop;

    public $call_comment;

    /** @var string Object creation date */
    public $date_add;

    /** @var string Object last modification date */
    public $date_upd;

    /** @var array Class variables and their validation types */
    public static $definition = [
        'primary' => 'id',
        'table' => 'omniva_int_manifest',
        'fields' => [
            'id_shop' =>             ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'manifest_number' =>     ['type' => self::TYPE_STRING, 'size' => 255],
            'date_add' =>            ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'date_upd' =>            ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            ]
    ];

    public static function checkManifestExists($cart_id)
    {
        return (bool) Db::getInstance()->getValue("SELECT id FROM " . _DB_PREFIX_ . self::$definition['table'] . " WHERE manifest_number = '$cart_id'");
    }

    /**
     * Get manifest by number.
     */
    public static function getManifestByNumber($manifest_number)
    {

        $query = (new DbQuery())
            ->select("id")
            ->from(self::$definition['table'])
            ->where("manifest_number = '$manifest_number'");

        $id_carrier = Db::getInstance()->getValue($query);
        if (!$id_carrier) {
            return false;
        }

        return new OmnivaIntManifest($manifest_number);
    }

}
