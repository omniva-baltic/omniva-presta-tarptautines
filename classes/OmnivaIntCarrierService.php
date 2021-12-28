<?php

class OmnivaIntCarrierService extends ObjectModel
{
    public $id;

    public $id_carrier;

    public $id_service;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'omniva_int_carrier_service',
        'primary' => 'id',
        'fields' => [
                'id_carrier' =>       ['type' => self::TYPE_INT, 'required' => true, 'size' => 10],
                'id_service' =>       ['type' => self::TYPE_INT, 'required' => true, 'size' => 10],
            ],
        ];

}
