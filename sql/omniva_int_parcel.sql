CREATE TABLE IF NOT EXISTS `_DB_PREFIX_omniva_int_parcel` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `id_shipment` int(10) NOT NULL,
    `amount` int(10) NOT NULL,
    `weight` float(10) NOT NULL,
    `length` float(10) NOT NULL,
    `width` float(10) NOT NULL,
    `height` float(10) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=_MYSQL_ENGINE_ DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
ALTER TABLE `_DB_PREFIX_omniva_int_parcel` ADD KEY `id_shipment` (`id_shipment`);