<?php

abstract class OmnivaIntModel extends ObjectModel
{
    public static function getCount()
    {
        $query = (new DbQuery())
        ->select("COUNT(*)")
        ->from(static::$definition['table']);
        $count = Db::getInstance()->getValue($query);
        if (!$count || $count <= 0) {
            return false;
        }
        return true;
    }
}