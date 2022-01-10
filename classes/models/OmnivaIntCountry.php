<?php

require_once 'OmnivaIntModel.php';

class OmnivaIntCountry extends OmnivaIntModel
{
    public $id;

    public $name;

    public $en_name;

    public $code;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'omniva_int_country',
        'primary' => 'id_country',
        'fields' => [
                'name' =>           ['type' => self::TYPE_STRING, 'size' => 255],
                'en_name' =>        ['type' => self::TYPE_STRING, 'size' => 255],
                'code' =>           ['type' => self::TYPE_STRING, 'size' => 3],
            ],
        ];


    public static function getCountryIdByIso($iso)
    {
        return (int) Db::getInstance()->getValue("SELECT id_country FROM " . _DB_PREFIX_ . self::$definition['table'] . " WHERE code = '$iso'");
    }
}