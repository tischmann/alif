-- Adminer 4.7.8 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `clients`;
CREATE TABLE `clients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phone` varchar(24) NOT NULL,
  `email` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `clients` (`id`, `phone`, `email`) VALUES
(1,	'+992938788228',	'yuriystolov@gmail.com'),
(2,	'+992985858228',	'username@gmail.com'),
(3,	'+992915758228',	'zetuser@gmail.com');

DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `price` float NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `products` (`id`, `name`, `price`) VALUES
(1,	'Apples',	10),
(2,	'Bananas',	17),
(3,	'Kiwi',	20),
(4,	'Pineapple',	25),
(5,	'Melon',	12);

-- 2021-02-13 06:39:23