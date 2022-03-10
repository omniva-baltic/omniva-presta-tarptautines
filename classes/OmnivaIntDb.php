<?php

class OmnivaIntDb
{
    const TABLES = [
        'omniva_int_carrier',
        'omniva_int_category',
        'omniva_int_manifest',
        'omniva_int_service',
        'omniva_int_order',
        'omniva_int_terminal',
        'omniva_int_carrier_service',
        'omniva_int_service_category',
        'omniva_int_country',
        'omniva_int_parcel',
        'omniva_int_cart_terminal',
        'omniva_int_rate_cache',
        'omniva_int_carrier_country'
    ];
    /**
     * Create tables for module
     */
    public function createTables()
    {
        $sql_path = dirname(__FILE__) . '/../sql/';
        $sql_files = scandir($sql_path);
        $sql_queries = [];
        foreach($sql_files as $sql_file)
        {
            $file_parts = pathinfo($sql_file);
            if($file_parts['extension'] == 'sql')
            {
                $sql_query = str_replace('_DB_PREFIX_', _DB_PREFIX_, Tools::file_get_contents($sql_path . $sql_file));
                $sql_queries[] = str_replace('_MYSQL_ENGINE_', _MYSQL_ENGINE_, $sql_query);
            }
        }
        foreach ($sql_queries as $query) {
            try {
                $res_query = Db::getInstance()->execute($query);

                if ($res_query === false) {
                    return false;
                }
            } catch (Exception $e) {
                return false;
            }
        }

        return true;
    }

    /**
     * Delete module tables
     */
    public function deleteTables()
    {
        foreach (self::TABLES as $table) {
            try {
                Db::getInstance()->execute("DROP TABLE IF EXISTS " . _DB_PREFIX_ . $table);
            } catch (Exception $e) {
            }
        }

        return true;
    }

}
