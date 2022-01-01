<?php

class OmnivaIntServiceCategory extends ObjectModel
{
    public $id;

    public $id_service;

    public $id_category;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'omniva_int_service_category',
        'primary' => 'id',
        'fields' => [
                'id_service' =>       ['type' => self::TYPE_INT, 'required' => true, 'size' => 10],
                'id_category' =>       ['type' => self::TYPE_INT, 'required' => true, 'size' => 10],
            ],
        ];

        public static function getServiceCategories($id_service)
        {
            $query = (new DbQuery())
                ->select("id_category")
                ->from(self::$definition['table'])
                ->where('id_service = ' . $id_service);

            return array_map(function($category) {
                 return $category['id_category'];
            }, Db::getInstance()->executeS($query));
        } 

        public static function getServiceCategoryId($id_service, $id_category)
        {
            $query = (new DbQuery())
                ->select("id")
                ->from(self::$definition['table'])
                ->where('id_service = ' . $id_service)
                ->where('id_category = ' . $id_category);

            return  Db::getInstance()->getValue($query);
        } 
}
