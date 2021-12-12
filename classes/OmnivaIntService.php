<?php

class OmnivaIntService extends ObjectModel
{
    public $id;

    public $code;

    public $insurance;

    public $return;

    public $carry_service;

    public $doc_return;

    /** @var string Object creation date */
    public $date_add;

    /** @var string Object last modification date */
    public $date_upd;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'omniva_int_service',
        'primary' => 'id',
        'fields' => [
                'code' =>             ['type' => self::TYPE_STRING, 'size' => 20],
                'insurance' =>        ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
                'return' =>           ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
                'carry_service' =>    ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
                'doc_return' =>       ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
                'date_add' =>         ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
                'date_upd' =>         ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            ],
        ];

}
