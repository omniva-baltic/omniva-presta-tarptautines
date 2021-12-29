CREATE TABLE IF NOT EXISTS `_DB_PREFIX_omniva_int_country` (
    `id_country` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(255),
    `en_name` varchar(25),
    `code` varchar(3),
    PRIMARY KEY (`id_country`)
) ENGINE=_MYSQL_ENGINE_ DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;