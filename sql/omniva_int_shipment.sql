CREATE TABLE IF NOT EXISTS `_DB_PREFIX_omniva_int_shipment` (
    `id_shipment` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `id_shop` int(10),
    `id_manifest` int(10),
    `cod` tinyint(1),
    `insurance` tinyint(1),
    `carry_service` tinyint(1),
    `doc_return` tinyint(1),
    `own_login` tinyint(1),
    `fragile` tinyint(1),
    `date_add` datetime NOT NULL,
    `date_upd` datetime NOT NULL,
    PRIMARY KEY (`id_shipment`)
) ENGINE=_MYSQL_ENGINE_ DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
ALTER TABLE `_DB_PREFIX_omniva_int_shipment` ADD KEY `id_shop` (`id_shop`), ADD KEY `id_manifest` (`id_manifest`);