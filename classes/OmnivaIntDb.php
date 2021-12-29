<?php

class OmnivaIntDb
{
    const TABLES = [
        'omniva_int_carrier',
        'omniva_int_category',
        'omniva_int_manifest',
        'omniva_int_service',
        'omniva_int_shipment',
        'omniva_int_terminal',
        'omniva_int_carrier_service',
        'omniva_int_service_category'
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
                $sql_query = str_replace('_DB_PREFIX_', _DB_PREFIX_, file_get_contents($sql_path . $sql_file));
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
                $res_query = Db::getInstance()->execute("DROP TABLE IF EXISTS " . _DB_PREFIX_ . $table);
            } catch (Exception $e) {
            }
        }

        return true;
    }

    /**
     * Check if table exists
     */
    private function checkTable($table)
    {
        $checker = Db::getInstance()->executeS('SHOW TABLES LIKE "' . pSQL($table) . '"');

        if (empty($checker)) {
            throw new Exception('Database table "' . $table . '" not exists.');
            return false;
        } else {
            return true;
        }
    }

    /**
     * Get value from table
     */
    public function getValue($table_name, $get_column, $where, $where_condition = 'AND')
    {
        if (
            !is_array($where)
            || !$this->checkTable(_DB_PREFIX_ . $table_name)
        ) {
            return false;
        }
        $sql_where = '';
        foreach ($where as $key => $value) {
            if (!empty($sql_where)) {
                $sql_where .= ' ' . pSQL($where_condition) . ' ';
            }
            $sql_where .= pSQL($key) . ' = ' . pSQL($value);
        }

        $result = Db::getInstance()->getValue("SELECT " . pSQL($get_column) . " FROM " . _DB_PREFIX_ . $table_name . " WHERE " . $sql_where);

        return $result;
    }

    /**
     * Get row from table
     */
    public function getRow($table_name, $get_column, $where, $where_condition = 'AND')
    {
        if (
            !is_array($where)
            || !$this->checkTable(_DB_PREFIX_ . $table_name)
        ) {
            return false;
        }
        $sql_where = '';
        foreach ($where as $key => $value) {
            if (!empty($sql_where)) {
                $sql_where .= ' ' . pSQL($where_condition) . ' ';
            }
            $sql_where .= pSQL($key) . ' = ' . pSQL($value);
        }

        $result = Db::getInstance()->getRow("SELECT " . pSQL($get_column) . " FROM " . _DB_PREFIX_ . $table_name . " WHERE " . $sql_where);

        return $result;
    }

    /**
     * Insert row to table
     */
    public function insertRow($table_name, $sql_values)
    {
        if (!$this->checkTable(_DB_PREFIX_ . $table_name)) {
            return false;
        }

        foreach ($sql_values as $key => $value) {
            $sql_values[$key] = pSQL(trim($value));
        }

        $result = Db::getInstance()->insert($table_name, $sql_values);

        return $result;
    }

    /**
     * Update row in table
     */
    public function updateRow($table_name, $sql_values, $where_values, $where_condition = 'AND')
    {
        if (
            !$this->checkTable(_DB_PREFIX_ . $table_name)
            || !$this->getOrderValue(1, $where_values)
        ) {
            return false;
        }

        foreach ($sql_values as $key => $value) {
            $sql_values[$key] = pSQL(trim($sql_values[$key]));
        }

        $sql_where = '';
        foreach ($where_values as $key => $value) {
            if (!empty($sql_where)) {
                $sql_where .= ' ' . pSQL($where_condition) . ' ';
            }
            $sql_where .= pSQL($key) . ' = ' . pSQL($value);
        }

        $result = Db::getInstance()->update($table_name, $sql_values, $sql_where);

        return $result;
    }

    /**
     * Get order id from module table
     */
    public function getOrderIdByCartId($cart_id)
    {
        if (!$this->checkTable(_DB_PREFIX_ . $this->_table_orders)) {
            return false;
        }

        $order_id = Db::getInstance()->getValue("SELECT id_order FROM " . _DB_PREFIX_ . $this->_table_orders . " WHERE id_cart = " . pSQL($cart_id));

        return $order_id;
    }

    /**
     * Get table value from module 'orders' table
     */
    public function getOrderValue($get_column, $where, $where_condition = 'AND')
    {
        return $this->getValue($this->_table_orders, $get_column, $where, $where_condition);
    }

    /**
     * Get table value from module 'manifests' table
     */
    public function getManifestValue($get_column, $where, $where_condition = 'AND')
    {
        return $this->getValue($this->_table_manifests, $get_column, $where, $where_condition);
    }

    /**
     * Insert row to module 'orders' table
     */
    public function saveOrderInfo($sql_values)
    {
        return $this->insertRow($this->_table_orders, $sql_values);
    }

    /**
     * Update row in module 'orders' table
     */
    public function updateOrderInfo($identifier, $sql_values, $where = 'id_cart')
    {
        return $this->updateRow($this->_table_orders, $sql_values, array($where => $identifier));
    }

    /**
     * Update row in module 'orders' table
     */
    public function getOrderInfo($order_id, $sql_values = '*')
    {
        return $this->getRow($this->_table_orders, $sql_values, array('id_order' => $order_id));
    }

}
