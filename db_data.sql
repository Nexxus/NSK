-- phpMyAdmin SQL Dump
-- version 4.7.7
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Mar 16, 2018 at 01:41 PM
-- Server version: 10.1.30-MariaDB
-- PHP Version: 5.6.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `copiproto`
--

--
-- Dumping data for table `attribute`
--

INSERT INTO `attribute` (`id`, `attr_code`, `name`) VALUES
(1, '', 'Model'),
(2, '', 'Behuizing'),
(3, '', 'Moederbord'),
(4, '', 'CPU'),
(5, '', 'CPU Snelheid'),
(6, '', 'Voeding'),
(7, '', 'Geheugen'),
(8, '', 'Hard Drive'),
(9, '', 'Grafische Kaart'),
(10, '', 'Besturingssysteem'),
(11, '', 'Software');

--
-- Dumping data for table `fos_user`
--

INSERT INTO `fos_user` (`id`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `salt`, `password`, `last_login`, `confirmation_token`, `password_requested_at`, `roles`, `firstname`, `lastname`, `role`) VALUES
(1, 'testadmin', 'testadmin', 'test@admin.nl', 'test@admin.nl', 1, NULL, '$2y$13$9iBoyzmrgKTm9Ig4wIll5O0inEz/Mbh/Sj6Wrnxl99DS6o3gRnp1K', '2018-03-16 13:08:44', NULL, NULL, 'a:1:{i:0;s:10:\"ROLE_ADMIN\";}', NULL, NULL, NULL),
(2, 'testuser', 'testuser', 'test1@admin.nl', 'test1@admin.nl', 1, NULL, '$2y$13$JORS3VsonUiQu.I3aQVGs.PwXrPlCjiPBuBvtg8Nohf2MQyqD8pfy', '2017-12-01 15:17:59', NULL, NULL, 'a:0:{}', NULL, NULL, NULL);

--
-- Dumping data for table `location`
--

INSERT INTO `location` (`id`, `name`) VALUES
(1, 'Kantoor Ametisthorst'),
(2, 'Loods Amsterdam'),
(3, 'Test Locatie123');

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`id`, `type`, `location`, `status`, `sku`, `name`, `description`, `brand`, `department`, `owner`, `quantity`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 2, '12345', 'Dell Optiplex 780 4GB 160GB', 'Een snelle C2D Computer', 'Dell', NULL, 1, 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(2, 1, 1, 7, '20899575', 'office', 'word', 'microsoft', NULL, NULL, NULL, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(3, NULL, 1, NULL, 'ean8 567', 'sku met spatie', 'sku', 'sku', NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(5, 3, 1, NULL, 'COPIA-4', 'Test Product', NULL, NULL, NULL, NULL, 1, '2017-12-11 14:47:47', '2017-12-11 14:47:47');

--
-- Dumping data for table `product_attribute`
--

INSERT INTO `product_attribute` (`id`, `product_id`, `attr_id`, `value`) VALUES
(1, 1, 1, 'Standaard'),
(2, 1, 2, NULL),
(3, 1, 3, NULL),
(4, 1, 4, NULL),
(5, 1, 5, NULL),
(6, 1, 6, NULL),
(7, 1, 7, NULL),
(8, 1, 8, NULL),
(9, 1, 9, NULL),
(10, 1, 10, NULL),
(11, 1, 11, NULL);

--
-- Dumping data for table `product_status`
--

INSERT INTO `product_status` (`id`, `pindex`, `name`) VALUES
(1, 1, 'Registered'),
(2, 2, 'Cleaned'),
(3, 3, 'In Repair'),
(4, 4, 'Check and/or Test OK'),
(5, 5, 'Refurbished/Imaged'),
(6, 6, 'Inventory/Stock'),
(7, 7, 'Ready For Sale');

--
-- Dumping data for table `product_type`
--

INSERT INTO `product_type` (`id`, `name`, `pindex`, `comment`) VALUES
(1, 'Computer', 1, NULL),
(2, 'Laptop', 2, NULL),
(3, 'Printer', 3, NULL),
(4, 'Misc', 10, NULL);

--
-- Dumping data for table `product_type_attribute`
--

INSERT INTO `product_type_attribute` (`id`, `attr_id`, `type_id`) VALUES
(1, 1, 1),
(2, 2, 1),
(3, 3, 1),
(4, 4, 1),
(5, 5, 1),
(6, 6, 1),
(7, 7, 1),
(8, 8, 1),
(9, 9, 1),
(10, 10, 1),
(11, 11, 1);

--
-- Dumping data for table `role`
--

INSERT INTO `role` (`id`, `name`) VALUES
(1, 'ROLE_ADMIN'),
(2, 'ROLE_USER');

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `username`, `password`, `email`, `firstname`, `lastname`, `role`, `is_active`) VALUES
(1, 'testadmin', '$2y$12$qeTJHj8IoWVe8IkLzktyd.0oz.IYw4hcVvfk/G4YoV/UUzixlKmFu', 'test@admin.com', 'Test', 'Admin', 0, 0);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
