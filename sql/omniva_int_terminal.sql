CREATE TABLE IF NOT EXISTS `_DB_PREFIX_omniva_int_terminal` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `terminal_id` varchar(10),
    `name` varchar(255),
    `city` varchar(100),
    `country_code` varchar(3),
    `address` varchar(255),
    `zip` varchar(10),
    `x_cord` float,
    `y_cord` float,
    `comment` varchar(255),
    `identifier` varchar(50),
    PRIMARY KEY (`id`)
) ENGINE=_MYSQL_ENGINE_ DEFAULT CHARSET=utf8;