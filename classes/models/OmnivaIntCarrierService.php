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


    public static function getCarrierServices($id_carrier)
    {
        $query = (new DbQuery())
            ->select("id_service")
            ->from(self::$definition['table'])
            ->where('id_carrier = ' . $id_carrier)
            ->orderBy('id_service ASC');

        return array_map(function($service) {
                return $service['id_service'];
        }, Db::getInstance()->executeS($query));
    }

    public static function getCarrierService($id_carrier, $id_service)
    {
        $query = (new DbQuery())
            ->select("id")
            ->from(self::$definition['table'])
            ->where('id_carrier = ' . $id_carrier)
            ->where('id_service = ' . $id_service);

        return  Db::getInstance()->getValue($query);
    }

    public static function getCarrierServicesCodes($id_carrier)
    {
        $query = (new DbQuery())
            ->select("os.service_code")
            ->from(self::$definition['table'], 'ocs')
            ->leftJoin('omniva_int_service', 'os', 'os.id = ocs.id_service')
            ->where('ocs.id_carrier = ' . $id_carrier);

        return array_map(function($service) {
                return $service['service_code'];
        }, Db::getInstance()->executeS($query));
    }

}
