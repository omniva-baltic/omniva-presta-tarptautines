<?php

class OmnivaIntService extends ObjectModel
{
    public $id;

    public $name;

    public $service_code;

    public $cod;

    public $insurance;

    public $carry_service;

    public $doc_return;

    public $own_login;

    public $fragile;

    public $manage_categories;

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
                'name' =>                       ['type' => self::TYPE_STRING, 'size' => 100],
                'service_code' =>               ['type' => self::TYPE_STRING, 'size' => 20],
                'image' =>                      ['type' => self::TYPE_STRING, 'size' => 255],
                'cod' =>                        ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
                'insurance' =>                  ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
                'carry_service' =>              ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
                'doc_return' =>                 ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
                'own_login' =>                  ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
                'fragile' =>                    ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
                'manage_categories' =>          ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
                'date_add' =>                   ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
                'date_upd' =>                   ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            ],
        ];
        
    public static function getServices()
    {
        $cacheId = 'OmnivaIntService::getServices';

        if (!Cache::isStored($cacheId)) {
            $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
				SELECT id, CONCAT(name, " - ", service_code) as name
				FROM ' . _DB_PREFIX_ . self::$definition['table']);
            Cache::store($cacheId, $result);
            return $result;
        }

        return Cache::retrieve($cacheId);
    }

    public static function checkServiceExists($id_service)
    {
        return (bool) Db::getInstance()->getValue("SELECT id FROM " . _DB_PREFIX_ . self::$definition['table'] . " WHERE id = " . $id_service);
    }

    public function toggleStatus()
    {
        $this->setFieldsToUpdate(['manage_categories' => true]);
        $this->manage_categories = !(int) $this->manage_categories;
        return $this->update(false);
    }
}
