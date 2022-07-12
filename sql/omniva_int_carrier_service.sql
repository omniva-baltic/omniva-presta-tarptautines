CREATE TABLE IF NOT EXISTS `_DB_PREFIX_omniva_int_carrier_service` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `id_carrier` int(10) unsigned NOT NULL,
    `id_service` int(10) unsigned NOT NULL,
    PRIMARY KEY (`id`),
    KEY `carrier_service` (`id_carrier`, `id_service`)
) ENGINE=_MYSQL_ENGINE_ DEFAULT CHARSET=utf8;