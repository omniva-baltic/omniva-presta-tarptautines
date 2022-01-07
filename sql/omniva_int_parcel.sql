CREATE TABLE IF NOT EXISTS `_DB_PREFIX_omniva_int_parcel` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `id_order` int(10) NOT NULL,
    `amount` int(10) NOT NULL,
    `weight` float(10) NOT NULL,
    `length` float(10) NOT NULL,
    `width` float(10) NOT NULL,
    `height` float(10) NOT NULL,
    `tracking_number` varchar(100) DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `id_shipment` (`id_shipment`)
) ENGINE=_MYSQL_ENGINE_ DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;