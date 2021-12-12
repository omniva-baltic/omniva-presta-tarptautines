CREATE TABLE IF NOT EXISTS `_DB_PREFIX_omniva_int_shipment` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `id_cart` int(10),
    `id_order` int(10),
    `id_shop` int(10),
    `id_terminal` int(10),
    `id_manifest` int(10),
    `shipment_weight` float(10),
    `date_add` datetime NOT NULL,
    `date_upd` datetime NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=_MYSQL_ENGINE_ DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;