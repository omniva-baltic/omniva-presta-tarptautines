CREATE TABLE IF NOT EXISTS `_DB_PREFIX_omniva_int_carrier` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `id_reference` int(10) unsigned NOT NULL,
    `price_type` varchar(30),
    `price` float(10),
    `free_shipping` float(10),
    `cheapest` tinyint(1),
    `type` varchar(30),
    `radius` int(10),
    `active` tinyint(1) DEFAULT 1,
    `date_add` datetime NOT NULL,
    `date_upd` datetime NOT NULL,
    PRIMARY KEY (`id`),
    KEY `id_reference` (`id_reference`)
) ENGINE=_MYSQL_ENGINE_ DEFAULT CHARSET=utf8;