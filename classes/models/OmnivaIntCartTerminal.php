<?php

class OmnivaIntCartTerminal extends ObjectModel
{
    public $id;

    public $id_terminal;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'omniva_int_cart_terminal',
        'primary' => 'id_cart',
        'fields' => [
            'id_terminal' =>   ['type' => self::TYPE_INT, 'required' => true, 'size' => 10],
        ],
        'associations' => [
            'terminal' => ['type' => self::HAS_ONE],
        ],
    ];

}
