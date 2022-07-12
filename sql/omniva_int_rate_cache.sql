CREATE TABLE IF NOT EXISTS `_DB_PREFIX_omniva_int_rate_cache` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `id_cart` int(10) unsigned NOT NULL,
    `hash` varchar(32) NOT NULL,
    `rate` float(10) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `cache_key` (`id_cart`, `hash`)
) ENGINE=_MYSQL_ENGINE_ DEFAULT CHARSET=utf8;