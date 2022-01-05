CREATE TABLE IF NOT EXISTS `_DB_PREFIX_omniva_int_cart_terminal` (
    `id_cart` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `id_terminal` int(10) NOT NULL,
    PRIMARY KEY (`id_cart`)
) ENGINE=_MYSQL_ENGINE_ DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
ALTER TABLE `_DB_PREFIX_omniva_int_cart_terminal` ADD KEY `id_terminal` (`id_terminal`);