CREATE TABLE IF NOT EXISTS `_DB_PREFIX_omniva_int_service_category` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `id_service` int(10) unsigned NOT NULL,
    `id_category` int(10) unsigned NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=_MYSQL_ENGINE_ DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;