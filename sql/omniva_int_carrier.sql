CREATE TABLE IF NOT EXISTS `_DB_PREFIX_omniva_int_carrier` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `id_carrier` int(10) unsigned NOT NULL,
    `price_type` varchar(30),
    `price` float(10),
    `free_shipping` float(10),
    `my_login` tinyint(1),
    `user` varchar(50),
    `password` varchar(50),
    `select_fastest` tinyint(1),
    `radius` int(10),
    `date_add` datetime NOT NULL,
    `date_upd` datetime NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=_MYSQL_ENGINE_ DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;