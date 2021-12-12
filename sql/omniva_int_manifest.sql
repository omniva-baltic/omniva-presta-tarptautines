CREATE TABLE IF NOT EXISTS `_DB_PREFIX_omniva_int_manifest` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `id_shop` int(10) unsigned NOT NULL,
    `manifest_number` varchar(50),
    `date_add` datetime NOT NULL,
    `date_upd` datetime NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=_MYSQL_ENGINE_ DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;