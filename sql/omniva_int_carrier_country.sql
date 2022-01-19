CREATE TABLE IF NOT EXISTS `_DB_PREFIX_omniva_int_carrier_country` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `id_carrier` int(10) unsigned NOT NULL,
    `id_country` int(10) unsigned NOT NULL,
    `price_type` varchar(30),
    `price` float(10),
    `free_shipping` float(10),
    `active` tinyint(1) DEFAULT 1,
    PRIMARY KEY (`id`),
    KEY `id_carrier` (`id_carrier`),
    KEY `id_country` (`id_country`)
    ) ENGINE=_MYSQL_ENGINE_ DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;