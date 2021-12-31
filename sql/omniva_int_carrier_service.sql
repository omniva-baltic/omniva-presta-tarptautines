CREATE TABLE IF NOT EXISTS `_DB_PREFIX_omniva_int_carrier_service` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `id_carrier` int(10) unsigned NOT NULL,
    `id_service` int(10) unsigned NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=_MYSQL_ENGINE_ DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
ALTER TABLE `_DB_PREFIX_omniva_int_carrier_service` ADD KEY `carrier_service` (`id_carrier`, `id_service`);