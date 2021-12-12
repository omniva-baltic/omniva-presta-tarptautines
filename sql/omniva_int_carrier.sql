CREATE TABLE IF NOT EXISTS `_DB_PREFIX_omniva_int_carrier` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `id_carrier` int(10) unsigned NOT NULL,
    `id_service` int(10) unsigned NOT NULL,
    `price_type` varchar(15),
    `price` float(10),
    `free_shipping` tinyint(1),
    `select_fastest` tinyint(1),
    `user_login` tinyint(1),
    `date_add` datetime NOT NULL,
    `date_upd` datetime NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=_MYSQL_ENGINE_ DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;