CREATE TABLE IF NOT EXISTS `_DB_PREFIX_omniva_int_terminal` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `code` varchar(50),
    `zip` varchar(10),
    `address` varchar(255),
    `city` varchar(50),
    `weight_limit` int(10),
    PRIMARY KEY (`id`)
) ENGINE=_MYSQL_ENGINE_ DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;