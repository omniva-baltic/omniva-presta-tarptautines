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

}
