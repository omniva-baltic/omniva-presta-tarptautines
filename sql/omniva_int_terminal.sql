CREATE TABLE IF NOT EXISTS `_DB_PREFIX_omniva_int_terminal` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(255),
    `city` varchar(100),
    `country_code` varchar(3),
    `address` varchar(255),
    `zipcode` varchar(10),
    PRIMARY KEY (`id`)
) ENGINE=_MYSQL_ENGINE_ DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;