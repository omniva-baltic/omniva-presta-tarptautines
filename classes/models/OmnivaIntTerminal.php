<?php

class OmnivaIntTerminal extends ObjectModel
{
    public $id;

    public $name;

    public $city;

    public $country_code;

    public $address;

    public $x_cord;

    public $y_cord;

    public $comment;

    public $identifier;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'omniva_int_terminal',
        'primary' => 'id',
        'fields' => [
                'name' =>           ['type' => self::TYPE_STRING, 'size' => 255],
                'city' =>           ['type' => self::TYPE_STRING, 'size' => 100],
                'country_code' =>   ['type' => self::TYPE_STRING, 'size' => 3],
                'address' =>        ['type' => self::TYPE_STRING, 'size' => 255],
                'x_cord' =>         ['type' => self::TYPE_FLOAT, 'size' => 100],
                'y_cord' =>         ['type' => self::TYPE_FLOAT, 'size' => 100],
                'comment' =>        ['type' => self::TYPE_STRING, 'size' => 255],
                'identifier' =>     ['type' => self::TYPE_STRING, 'size' => 50],
            ],
        ];

    public static function getTerminalsByIsoAndIndentifier($iso, $identifier, $city = false, $groupBy = false)
    {
        $where = "country_code = '$iso' AND identifier = '$identifier'";
        if($city)
        {
            $where .= " AND city = '$city'";
        }
        $query = (new DbQuery())
            ->select("*, CONCAT(name, ', ', address) as name")
            ->from(self::$definition['table'])
            ->where($where);

        if($groupBy)
            $query->groupBy($groupBy);
        return Db::getInstance()->executeS($query);
    }
}