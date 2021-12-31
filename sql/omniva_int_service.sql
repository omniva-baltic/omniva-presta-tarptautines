CREATE TABLE IF NOT EXISTS `_DB_PREFIX_omniva_int_service` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(100),
    `service_code` varchar(20),
    `image` varchar(255) DEFAULT NULL,
    `cod` tinyint(1),
    `insurance` tinyint(1),
    `carry_service` tinyint(1),
    `doc_return` tinyint(1),
    `own_login` tinyint(1),
    `fragile` tinyint(1),
    `date_add` datetime NOT NULL,
    `date_upd` datetime NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=_MYSQL_ENGINE_ DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;