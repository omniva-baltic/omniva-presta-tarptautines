CREATE TABLE IF NOT EXISTS `_DB_PREFIX_omniva_int_category` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `id_category` int(10) unsigned NOT NULL,
    `weight` float(10),
    `length` float(10),
    `width` float(10),
    `height` float(10),
    `active` tinyint(1),
    PRIMARY KEY (`id`)
) ENGINE=_MYSQL_ENGINE_ DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;