-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 02, 2025 at 11:28 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tukocart`
--

-- --------------------------------------------------------

--
-- Table structure for table `buyers`
--

CREATE TABLE `buyers` (
  `buyerID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `shippingAddress1` varchar(255) DEFAULT NULL,
  `shippingAddress2` varchar(255) DEFAULT NULL,
  `postalCode` varchar(10) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `buyers`
--

INSERT INTO `buyers` (`buyerID`, `userID`, `shippingAddress1`, `shippingAddress2`, `postalCode`, `created_at`) VALUES
(1, 2, '123 Greenfield Street', 'Cape Town', '8002', '2025-05-07 16:16:49'),
(3, 5, '78 Rose Avenue', 'Pretoria', '6001', '2025-05-07 16:16:49'),
(4, 6, '12 Lavender Lane', 'Johannesburg', '1619', '2025-05-07 16:16:49'),
(5, 8, '99 Craft Street', 'Polokwane', '7100', '2025-05-07 16:16:49'),
(6, 10, '22 Artisan Blvd', 'Port Elizabeth', '4001', '2025-05-07 16:16:49'),
(7, 11, '101 Mandela Drive', 'East London', '9001', '2025-05-07 16:16:49'),
(8, 13, '67 Ubuntu Close', 'Bloemfontein', '1401', '2025-05-08 16:16:49'),
(9, 15, '5 Township Trail', 'Kimberley', '3201', '2025-05-07 16:16:49'),
(10, 18, '10 Karoo Crescent', 'Nelspruit', '2300', '2025-05-07 16:16:49'),
(11, 22, '31 Mooiplaas Road', 'Pretoria', '0081', '2025-05-28 16:16:49'),
(26, 24, '131 Griffiths Road', 'Pretoria', '0184', '2025-05-08 16:16:49'),
(27, 27, '123 Trunk Street', 'Pretoria', '0081', '2025-05-28 16:16:49'),
(30, 28, '54 Garden Road', 'Pretoria', '1024', '2025-06-02 14:32:02'),
(31, 33, '13 Gold Street', 'Pretoria', '0081', '2025-06-18 11:39:14');

-- --------------------------------------------------------

--
-- Table structure for table `cartitems`
--

CREATE TABLE `cartitems` (
  `cartItemID` int(11) NOT NULL,
  `cartID` int(11) NOT NULL,
  `productID` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `size` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cartitems`
--

INSERT INTO `cartitems` (`cartItemID`, `cartID`, `productID`, `quantity`, `size`) VALUES
(8, 12, 36, 1, 'UK 5');

-- --------------------------------------------------------

--
-- Table structure for table `carts`
--

CREATE TABLE `carts` (
  `cartID` int(11) NOT NULL,
  `buyerID` int(11) NOT NULL,
  `creationDate` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `carts`
--

INSERT INTO `carts` (`cartID`, `buyerID`, `creationDate`) VALUES
(9, 27, '2025-05-26 09:36:09'),
(10, 6, '2025-05-28 14:49:02'),
(11, 7, '2025-05-28 15:05:32'),
(12, 11, '2025-05-31 11:20:28'),
(13, 30, '2025-06-02 12:37:51'),
(14, 31, '2025-06-18 09:39:33');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `categoryID` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `parentID` int(11) DEFAULT NULL,
  `imagePath` varchar(44) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`categoryID`, `name`, `description`, `parentID`, `imagePath`) VALUES
(1, 'Handmade Crafts', 'Unique, handcrafted items created by local artisans', NULL, NULL),
(2, 'Home Decor', 'Products to add a personalized touch to your home', NULL, NULL),
(3, 'Clothing & Accessories', 'Stylish and fashionable clothing and accessories for all occasions', NULL, NULL),
(4, 'Jewelry', 'Custom-made and unique jewelry pieces for all occasions', NULL, NULL),
(5, 'Art & Collectibles', 'Original art, prints, and collectible pieces from local artists', NULL, NULL),
(6, 'Health & Beauty', 'Natural and organic beauty products for skin, hair, and more', NULL, NULL),
(8, 'Toys & Games', 'Fun and educational toys for children of all ages', NULL, NULL),
(11, 'Wooden Furniture', 'Handcrafted wooden furniture pieces for home and office', 1, 'cWoodFurniture.jpg'),
(12, 'Handmade Pottery', 'Unique pottery pieces made by local artisans', 1, 'placeholder.png'),
(13, 'Wall Art', 'Framed prints, posters, and art for home décor', 2, 'cwallart.jpg'),
(14, 'Indoor Plants', 'Indoor plants and planters for home decoration', 2, 'placeholder.png'),
(15, 'Rugs & Cushions', 'Decorative rugs and cushions to enhance your home', 2, 'placeholder.png'),
(17, 'Men\'s Fashion', 'Stylish clothing for men', 3, 'cMensFashion.jpg'),
(18, 'Women\'s Fashion', 'Trendy clothing for women', 3, 'placeholder.png'),
(19, 'Kids\' Clothing', 'Fun and trendy clothing for children', 3, 'placeholder.png'),
(20, 'Bags & Accessories', 'Handmade bags, belts, and accessories', 3, 'placeholder.png'),
(21, 'Necklaces & Chains', 'Custom-made necklaces and chains', 4, 'cnecklace.jpg'),
(22, 'Bracelets & Bangles', 'Unique bracelets and bangles', 4, 'placeholder.png'),
(23, 'Earrings', 'Handmade earrings for every occasion', 4, 'placeholder.png'),
(24, 'Rings', 'Custom-designed rings for all occasions', 4, 'placeholder.png'),
(25, 'Paintings', 'Original paintings by local artists', 5, 'cpaintings.jpg'),
(26, 'Sculptures', 'Unique sculptures made from various materials', 5, 'placeholder.png'),
(27, 'Vintage Collectibles', 'Vintage items and memorabilia for collectors', 5, 'placeholder.png'),
(28, 'Organic Skincare', 'Natural skincare made from organic ingredients', 6, 'cSkincare.jpg'),
(29, 'Hair Care', 'Hair care products for healthy and shiny hair', 6, 'placeholder.png'),
(30, 'Bath & Body', 'Bath essentials including soaps, scrubs, and lotions.', 6, 'placeholder.png'),
(31, 'Makeup & Cosmetics', 'Cosmetic products for a flawless look', 6, 'placeholder.png'),
(36, 'Educational Toys', 'Toys that encourage learning and creativity', 8, 'placeholder.png'),
(37, 'Outdoor Toys', 'Toys for outdoor play and adventure', 8, 'placeholder.png'),
(38, 'Board Games', 'Fun and interactive board games for all ages', 8, 'placeholder.png'),
(39, 'Puzzles', 'Challenging puzzles for children and adults', 8, 'placeholder.png'),
(44, 'Footwear', 'Step into style and innovation with our unique range of footwear crafted by local entrepreneurs.', 3, 'placeholder.png'),
(45, 'Handcrafted stationary', 'Beautifully crafted handmade stationery designed by passionate entrepreneurs, perfect for adding a personal and artistic touch to your writing.', 1, 'placeholder.png'),
(46, 'Wooden crafts', 'Handcrafted by skilled entrepreneurs, blending natural beauty with creative artistry.', 1, 'placeholder.png'),
(47, 'Decor Knick-Knacks ', 'Charming knick-knacks made by passionate entrepreneurs, perfect for adding personality and warmth to any space.', 2, 'placeholder.png'),
(48, 'Seasonal Decorations', 'Festive items for holidays and special occasions', 2, '6826f5a2a7c2a.png'),
(49, 'Candles & Scents', 'Aromatic candles and handcrafted scents that add warmth, relaxation, and personality to any space', 2, '6826f6fb06f62.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `deliveries`
--

CREATE TABLE `deliveries` (
  `deliveryID` int(11) NOT NULL,
  `orderID` int(11) NOT NULL,
  `deliveryStatus` varchar(50) DEFAULT 'pending',
  `deliveryDate` datetime DEFAULT NULL,
  `dispatchDate` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `deliveries`
--

INSERT INTO `deliveries` (`deliveryID`, `orderID`, `deliveryStatus`, `deliveryDate`, `dispatchDate`) VALUES
(20, 158, 'delivered', '2025-05-28 12:06:56', '2025-05-26 12:06:56'),
(21, 162, 'delivered', '2025-05-30 16:48:52', '2025-05-28 16:48:52'),
(22, 170, 'delivered', '2025-05-30 17:05:15', '2025-05-28 17:05:15'),
(23, 172, 'delivered', '2025-05-30 17:06:21', '2025-05-28 17:06:21'),
(24, 180, 'pending', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `feedbackID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `category` enum('Feedback','Complaint','Suggestion','Bug','Other') DEFAULT 'Feedback',
  `status` enum('Pending','In Progress','Resolved','Dismissed') DEFAULT 'Pending',
  `adminReply` text DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`feedbackID`, `userID`, `subject`, `message`, `category`, `status`, `adminReply`, `createdAt`, `updatedAt`) VALUES
(1, 14, 'Dark Mode', 'I would love to have a darkmode option for the website.', 'Suggestion', 'Pending', NULL, '2025-05-29 08:41:08', '2025-06-03 16:59:26'),
(2, 7, 'App Navigation Confusion', 'I couldn’t figure out how to access my past orders. It would help if the navigation menu had clearer labels.', 'Suggestion', 'Resolved', 'Thank you for the feedback. We’ve updated the menu with clearer labels, and added a \'My Orders\' button for quick access.', '2025-05-29 13:04:21', '2025-06-03 16:59:31'),
(3, 22, 'Product Listing Error', 'One of my products shows the wrong price after updating. Please assist.', 'Bug', 'In Progress', 'We’ve reviewed the issue and fixed the pricing sync for your product. Please clear cache and verify again.', '2025-05-29 13:06:34', '2025-06-03 16:58:19'),
(4, 18, 'Rating Not Saving', 'I tried to leave a review for a seller but the rating keeps resetting to 0 stars', 'Bug', 'Pending', NULL, '2025-05-29 13:08:16', '2025-06-03 16:59:55');

-- --------------------------------------------------------

--
-- Table structure for table `orderitems`
--

CREATE TABLE `orderitems` (
  `orderItemID` int(11) NOT NULL,
  `orderID` int(11) NOT NULL,
  `productID` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `size` varchar(25) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orderitems`
--

INSERT INTO `orderitems` (`orderItemID`, `orderID`, `productID`, `quantity`, `price`, `size`) VALUES
(1, 158, 72, 1, 549.99, 'UK 5'),
(2, 159, 48, 1, 250.00, 'S'),
(3, 160, 51, 1, 180.00, 'One Size'),
(4, 160, 52, 1, 150.00, 'One Size'),
(5, 161, 34, 1, 180.00, 'One Size'),
(6, 162, 57, 1, 350.00, 'One Size'),
(7, 162, 58, 1, 120.00, 'One Size'),
(8, 169, 46, 1, 180.00, 'One Size'),
(9, 170, 38, 1, 250.00, 'One Size'),
(10, 171, 35, 2, 400.00, 'S'),
(11, 172, 63, 1, 600.00, 'UK 7'),
(12, 172, 72, 1, 549.99, 'UK 6'),
(13, 173, 51, 1, 180.00, 'One Size'),
(14, 174, 36, 1, 250.00, 'UK 6'),
(15, 175, 51, 1, 180.00, 'One Size'),
(16, 176, 35, 1, 400.00, 'S'),
(17, 177, 35, 1, 400.00, 'S'),
(18, 178, 70, 1, 640.00, 'UK 5'),
(19, 179, 38, 1, 250.00, 'One Size'),
(20, 180, 63, 1, 600.00, 'UK 8'),
(21, 181, 34, 1, 180.00, 'One Size'),
(22, 182, 63, 1, 600.00, 'UK 5'),
(23, 183, 56, 1, 220.00, 'One Size'),
(24, 184, 46, 1, 180.00, 'One Size'),
(25, 185, 56, 1, 220.00, 'One Size'),
(26, 186, 77, 1, 250.00, 'One Size'),
(27, 187, 49, 1, 450.00, 'M'),
(28, 188, 63, 1, 600.00, 'UK 5'),
(29, 189, 36, 1, 250.00, 'UK 5'),
(30, 190, 36, 1, 250.00, 'UK 5'),
(31, 191, 36, 1, 250.00, 'UK 5'),
(32, 192, 36, 1, 250.00, 'UK 5'),
(33, 193, 36, 1, 250.00, 'UK 5'),
(34, 194, 36, 1, 250.00, 'UK 5'),
(35, 195, 36, 1, 250.00, 'UK 5');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `orderID` int(11) NOT NULL,
  `buyerID` int(11) NOT NULL,
  `orderDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `totalAmount` decimal(10,2) NOT NULL,
  `orderStatus` varchar(20) DEFAULT 'Pending',
  `sellerID` int(11) NOT NULL,
  `deliveryFee` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`orderID`, `buyerID`, `orderDate`, `totalAmount`, `orderStatus`, `sellerID`, `deliveryFee`) VALUES
(158, 27, '2025-05-26 09:41:18', 549.99, 'Completed', 18, 45.00),
(159, 27, '2025-05-26 09:41:18', 250.00, 'Paid', 7, 45.00),
(160, 27, '2025-05-28 13:12:02', 330.00, 'Paid', 8, 50.00),
(161, 11, '2025-05-28 14:46:39', 180.00, 'Paid', 2, 45.00),
(162, 11, '2025-05-28 14:46:39', 470.00, 'Completed', 10, 50.00),
(169, 6, '2025-05-28 15:03:03', 180.00, 'Paid', 5, 45.00),
(170, 6, '2025-05-28 15:03:03', 250.00, 'Completed', 3, 45.00),
(171, 6, '2025-05-28 15:03:44', 800.00, 'Paid', 2, 50.00),
(172, 7, '2025-05-28 15:05:45', 1149.99, 'Completed', 18, 50.00),
(173, 11, '2025-05-28 15:06:45', 180.00, 'Refunded', 8, 45.00),
(174, 11, '2025-05-31 11:30:35', 250.00, 'Paid', 2, 45.00),
(175, 11, '2025-05-31 11:36:53', 180.00, 'Paid', 8, 45.00),
(176, 11, '2025-06-01 14:32:31', 400.00, 'Pending', 2, 45.00),
(177, 11, '2025-06-02 07:52:31', 400.00, 'Paid', 2, 45.00),
(178, 11, '2025-06-02 07:52:31', 640.00, 'Refunded', 18, 45.00),
(179, 11, '2025-06-02 10:00:19', 250.00, 'Paid', 3, 45.00),
(180, 11, '2025-06-02 10:00:19', 600.00, 'Accepted', 18, 45.00),
(181, 30, '2025-06-02 12:38:07', 180.00, 'Paid', 2, 45.00),
(182, 11, '2025-06-03 07:54:55', 600.00, 'Refunded', 18, 45.00),
(183, 11, '2025-06-03 08:02:30', 220.00, 'Refunded', 10, 45.00),
(184, 11, '2025-06-03 08:09:14', 180.00, 'Refunded', 5, 45.00),
(185, 11, '2025-06-10 10:45:55', 220.00, 'Paid', 10, 45.00),
(186, 11, '2025-06-10 10:45:55', 250.00, 'Paid', 28, 45.00),
(187, 11, '2025-06-10 10:45:55', 450.00, 'Paid', 7, 45.00),
(188, 11, '2025-06-10 10:45:55', 600.00, 'Paid', 18, 45.00),
(189, 11, '2025-06-10 10:49:46', 250.00, 'Cancelled', 2, 45.00),
(190, 11, '2025-06-10 12:21:05', 250.00, 'Pending', 2, 45.00),
(191, 11, '2025-06-10 12:28:55', 250.00, 'Pending', 2, 45.00),
(192, 11, '2025-06-10 12:29:16', 250.00, 'Cancelled', 2, 45.00),
(193, 11, '2025-06-10 12:39:46', 250.00, 'Pending', 2, 45.00),
(194, 11, '2025-06-10 12:46:46', 250.00, 'Pending', 2, 45.00),
(195, 11, '2025-06-10 12:51:44', 250.00, 'Pending', 2, 45.00);

-- --------------------------------------------------------

--
-- Table structure for table `paymentrecords`
--

CREATE TABLE `paymentrecords` (
  `paymentID` int(11) NOT NULL,
  `orderID` int(11) DEFAULT NULL,
  `paymentDate` datetime NOT NULL,
  `paymentStatus` enum('Pending','Paid','Refunded') NOT NULL,
  `paymentAmount` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `paymentrecords`
--

INSERT INTO `paymentrecords` (`paymentID`, `orderID`, `paymentDate`, `paymentStatus`, `paymentAmount`) VALUES
(87, 158, '2025-05-26 11:42:23', 'Paid', 594.99),
(88, 159, '2025-05-26 11:42:23', 'Paid', 295.00),
(89, 160, '2025-05-28 15:12:48', 'Paid', 380.00),
(90, 161, '2025-05-28 16:46:55', 'Paid', 225.00),
(91, 162, '2025-05-28 16:46:55', 'Paid', 520.00),
(92, 169, '2025-05-28 17:03:28', 'Paid', 225.00),
(93, 170, '2025-05-28 17:03:28', 'Paid', 295.00),
(94, 171, '2025-05-28 17:03:57', 'Paid', 850.00),
(95, 172, '2025-05-28 17:05:56', 'Paid', 1199.99),
(96, 173, '2025-05-28 17:07:00', 'Refunded', -225.00),
(97, 174, '2025-05-31 13:31:06', 'Paid', 295.00),
(98, 175, '2025-05-31 13:37:06', 'Paid', 225.00),
(99, 177, '2025-06-02 09:52:54', 'Paid', 445.00),
(100, 178, '2025-06-02 09:52:54', 'Refunded', -685.00),
(101, 179, '2025-06-02 12:00:43', 'Paid', 295.00),
(102, 180, '2025-06-02 12:00:43', 'Paid', 645.00),
(103, 181, '2025-06-02 14:38:25', 'Paid', 225.00),
(104, 182, '2025-06-03 09:55:50', 'Paid', 645.00),
(105, 183, '2025-06-03 10:04:04', 'Paid', 265.00),
(106, 184, '2025-06-03 10:09:46', 'Paid', 225.00),
(107, 184, '2025-06-06 07:15:21', 'Refunded', -225.00),
(108, 183, '2025-06-06 07:19:16', 'Refunded', -265.00),
(109, 182, '2025-06-06 11:58:36', 'Refunded', -645.00),
(110, 185, '2025-06-10 12:47:22', 'Paid', 265.00),
(111, 186, '2025-06-10 12:47:22', 'Paid', 295.00),
(112, 187, '2025-06-10 12:47:22', 'Paid', 495.00),
(113, 188, '2025-06-10 12:47:22', 'Paid', 645.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `productID` int(11) NOT NULL,
  `sellerID` int(11) NOT NULL,
  `pCategory` int(40) DEFAULT NULL,
  `pName` varchar(255) NOT NULL,
  `pDescription` text DEFAULT NULL,
  `pPrice` decimal(10,2) NOT NULL,
  `imagePath` varchar(44) DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `seasonalTag` varchar(25) DEFAULT NULL,
  `status` varchar(10) DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`productID`, `sellerID`, `pCategory`, `pName`, `pDescription`, `pPrice`, `imagePath`, `createdAt`, `seasonalTag`, `status`) VALUES
(34, 2, 21, 'Zulu Beaded Hand Chain', 'A unique handmade hand chain with traditional Zulu beading patterns.', 180.00, 'zulubead.jpg', '2025-06-12 10:38:20', 'Mother\'s Day', 'active'),
(35, 2, 17, 'Zulu Inspired Shirt', 'A stylish shirt made from traditional Zulu fabric with modern cuts.', 400.00, 'zulushirt.png', '2025-06-12 10:34:29', NULL, 'active'),
(36, 2, 44, 'Handcrafted Zulu Inspired Heels', 'Leather heels with intricate Zulu beadwork designs.', 250.00, 'zuluheels.jpg', '2025-06-12 10:34:29', NULL, 'active'),
(37, 3, 12, 'Handmade Ceramic Mug', 'A beautifully crafted ceramic mug, perfect for any occasion.', 120.00, 'ceramicmug.jpg', '2025-06-12 10:38:32', 'Mother\'s Day', 'active'),
(38, 3, 12, 'Custom Ceramic Vase', 'A unique, handcrafted vase made by local artists.', 250.00, 'ceramicvase.jpg', '2025-06-12 10:43:35', 'Mother\'s Day', 'active'),
(39, 3, 12, 'Ceramic Plant Pot', 'A custom-designed ceramic pot for your indoor plants.', 150.00, 'ceramicpot.jpg', '2025-06-12 10:34:29', NULL, 'active'),
(43, 5, 45, 'Handmade Journal', 'A custom-designed journal with traditional African print covers.', 220.00, 'journal.jpg', '2025-06-12 10:45:00', 'Mother\'s Day', 'active'),
(44, 5, 45, 'African Print Greeting Cards', 'Set of beautifully crafted greeting cards with African prints.', 150.00, 'greetingcard.jpg', '2025-06-12 10:34:29', NULL, 'active'),
(45, 5, 45, 'African Print Stationery Set', 'A complete stationery set with traditional African patterns.', 300.00, 'pencils.jpg', '2025-06-12 10:34:29', NULL, 'active'),
(46, 5, 46, 'Wooden Cutlery Set', 'Handcrafted wooden cutlery  including fork, knife and spoon', 180.00, 'woodcutlery.jpg', '2025-06-12 10:34:29', NULL, 'active'),
(47, 5, 46, 'Wooden Braai Tongs', 'Durable and beautifully crafted wooden tongs for your braai.', 200.00, 'tongs.jpg', '2025-06-12 10:34:29', NULL, 'active'),
(48, 7, 18, 'Street Art T-Shirt', 'A unique t-shirt design inspired by urban street art.', 250.00, 'streettshirt.jpg', '2025-06-12 10:34:29', NULL, 'active'),
(49, 7, 17, 'Graphic Print Hoodie', 'A hoodie with a bold graphic print showcasing local street art.', 450.00, 'graphichoodie.jpg', '2025-06-12 10:34:29', NULL, 'active'),
(51, 8, 49, 'Lavender Soy Candle', 'A calming lavender scented soy candle made from local ingredients.', 180.00, 'lavendercandle.jpg', '2025-06-12 10:34:29', NULL, 'active'),
(52, 8, 49, 'Rosemary and Mint Candle', 'A refreshing candle with the soothing scent of rosemary and mint.', 150.00, 'mintcandle.jpg', '2025-06-12 10:43:45', 'Mother\'s Day', 'active'),
(54, 9, 44, 'Sneaker Cleaning Kit', 'A complete sneaker cleaning kit with brushes and cleaning solutions.', 350.00, 'sneakerclean.jpg', '2025-06-12 10:34:29', NULL, 'active'),
(55, 9, 44, 'Waterproof Sneaker Spray', 'Protect your sneakers with this waterproofing spray.', 200.00, 'waterproofsneaker.jpg', '2025-06-12 10:34:29', NULL, 'active'),
(56, 10, 28, 'Rooibos Skin Cream', 'A moisturizing cream made with natural Rooibos extract.', 220.00, 'cream.jpg', '2025-06-12 10:34:29', NULL, 'active'),
(57, 10, 28, 'Marula Oil Face Serum', 'A rejuvenating face serum made with pure Marula oil.', 350.00, 'serum.jpg', '2025-06-12 10:34:29', NULL, 'active'),
(58, 10, 28, 'Rooibos and Honey Soap', 'A luxurious soap made with Rooibos and honey for soft skin.', 120.00, 'rooisoap.jpg', '2025-06-12 10:34:29', NULL, 'active'),
(63, 18, 44, 'Vellies', 'Vellies is a durable shoe made from genuine leather with rubber soles.', 600.00, 'img_682c5afb744ca2.39902661.jpg', '2025-06-12 10:34:29', NULL, 'active'),
(70, 18, 44, 'Chelsea Boots', 'Chelsea Boots', 640.00, 'chelsea boots.jpg', '2025-06-12 10:34:29', NULL, 'active'),
(71, 18, 44, 'Plakkies', 'Plakkies', 50.00, 'img_6814e9cde80724.16121221.jpg', '2025-06-12 10:34:29', NULL, 'active'),
(72, 18, 44, 'Unisex Iviwe Leather Sandal', 'Locally made from premium-quality leather, our Iviwe Sandal is truly worth the investment. It features a sleek, minimalistic design and a buckle strap to ensure a secure fit. Wear it with everything from skirts to dresses and pants to shorts.', 549.99, 'sandel.jpg', '2025-06-12 10:34:29', NULL, 'active'),
(73, 18, 44, 'Basic Dual Color Sneakers', 'Designed for everyday wear, these sneakers feature a sleek dual-tone color scheme that pairs perfectly with any casual outfit. The lightweight design, cushioned insole, and durable rubber sole provide all-day support, making them ideal for walking, commuting, or just kicking back in style.', 400.00, 'sneaker.jpg', '2025-06-12 10:34:29', NULL, 'active'),
(75, 18, 44, 'Katy Heels', 'Cute stilleto heels in nude', 450.00, 'emily-pottiger-Zx76sbAndc0-unsplash.jpg', '2025-06-12 10:43:56', 'Mother\'s Day', 'active'),
(77, 28, 36, 'Racing Animals Wood Toys Set of 4', 'Set includes 4 animal racers: lion, elephant, zebra, and giraffe.\r\nMade from high-quality, durable wood.\r\nNon-toxic, child-safe paint.\r\nEncourages creativity, fine motor development, and active play.\r\nRecommended for ages 18 months and up.', 250.00, 'lisanto-KdvBcLjjo7A-unsplash.jpg', '2025-06-12 10:34:29', NULL, 'active'),
(78, 28, 38, 'Jenga Blocks', 'Bring excitement and challenge to any gathering with this classic Jenga Blocks game. Crafted from high-quality, smooth-finished hardwood, this set includes 54 precision-cut rectangular blocks stacked into a tower of suspense. The goal is simple: remove one block at a time and stack it on top—without letting the tower fall!', 200.00, 'mina-rad-PqoZE5tBuMw-unsplash.jpg', '2025-06-12 10:34:29', NULL, 'active'),
(102, 18, 44, 'Leather Winter Boots', 'Step confidently into the colder months with our premium Leather Winter Boots — expertly crafted for durability, comfort, and timeless style. These boots feature genuine full-grain leather uppers, a warm insulated lining, and a rugged non-slip sole designed to handle wet and icy conditions. Whether you\'re navigating city streets or countryside trails, these boots offer unmatched protection against the elements without compromising on fashion.', 800.00, 'nathan-dumlao-qxcQG21m_qE-unsplash.jpg', '2025-06-12 10:34:29', NULL, 'active'),
(103, 31, 25, 'Birds in Bloom', 'Capture the delicate harmony of nature with this soft watercolor painting of birds in flight. Rendered in calming hues, this artwork brings a serene, airy presence to any space. Perfect for bird lovers and nature enthusiasts seeking a gentle touch of wilderness indoors.', 200.00, 'img_6835c07a0283b0.20280362.jpg', '2025-06-12 10:34:29', NULL, 'active'),
(104, 31, 26, 'Tribal Essence', 'A striking wooden face sculpture inspired by African tribal art, blending natural texture with soulful expression. This piece brings warmth, identity, and a touch of global sophistication to modern living spaces.', 600.00, 'faceart.jpg', '2025-06-12 10:34:29', NULL, 'active'),
(105, 31, 26, 'Zulu Unity – Set of Two Hand-Carved Wooden Statues', 'Celebrate the strength, harmony, and artistry of Zulu heritage with this striking set of two hand-carved wooden statues. Each figure is sculpted from sustainably sourced African hardwood, featuring distinct traditional detailing and symbolic posture that reflect unity, leadership, and ancestral pride. Whether displayed as a centerpiece or placed on either side of a mantel or shelf, these statues bring a bold cultural presence and timeless elegance to any space.', 550.00, 'zulustatue.jpg', '2025-06-12 10:34:29', NULL, 'active'),
(106, 31, 25, 'Sunrise Pulse – Abstract Canvas in Yellow, Orange and Turquoise', 'Infuse your space with energy and movement through Sunrise Pulse, a bold abstract canvas that captures the vibrant rhythm of color. Layers of golden yellow, fiery orange, and cool turquoise collide and flow in modern harmony, evoking warmth, creativity, and balance. Perfect for contemporary interiors, this statement piece brings a pop of personality to any room — whether it\'s your living room, studio, or workspace.\r\n\r\n- Oil on canvas\r\n-  420 x 594 mm', 700.00, 'modernart.jpg', '2025-06-12 10:34:29', NULL, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `product_variants`
--

CREATE TABLE `product_variants` (
  `variantID` int(11) NOT NULL,
  `productID` int(11) NOT NULL,
  `size` varchar(20) NOT NULL,
  `stockQuantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_variants`
--

INSERT INTO `product_variants` (`variantID`, `productID`, `size`, `stockQuantity`) VALUES
(89, 102, 'UK 6', 7),
(90, 102, 'UK 7', 5),
(91, 102, 'UK 8', 5),
(92, 102, 'UK 9', 4),
(93, 34, 'One Size', 13),
(96, 37, 'One Size', 15),
(97, 38, 'One Size', 12),
(98, 39, 'One Size', 15),
(99, 43, 'One Size', 15),
(100, 44, 'One Size', 15),
(101, 45, 'One Size', 15),
(102, 46, 'One Size', 13),
(103, 47, 'One Size', 15),
(104, 48, 'S', 14),
(107, 51, 'One Size', 12),
(108, 52, 'One Size', 14),
(110, 54, 'One Size', 15),
(111, 55, 'One Size', 15),
(112, 56, 'One Size', 13),
(113, 57, 'One Size', 14),
(114, 58, 'One Size', 14),
(121, 77, 'One Size', 14),
(122, 78, 'One Size', 15),
(124, 63, 'UK 5', 13),
(125, 70, 'UK 5', 14),
(126, 71, 'UK 5', 15),
(127, 72, 'UK 5', 14),
(128, 73, 'UK 5', 14),
(129, 75, 'UK 5', 15),
(130, 102, 'UK 5', 15),
(131, 63, 'UK 6', 0),
(132, 70, 'UK 6', 15),
(133, 71, 'UK 6', 15),
(134, 72, 'UK 6', 14),
(135, 73, 'UK 6', 13),
(136, 75, 'UK 6', 15),
(137, 63, 'UK 7', 14),
(138, 70, 'UK 7', 15),
(139, 71, 'UK 7', 15),
(140, 72, 'UK 7', 15),
(141, 73, 'UK 7', 14),
(142, 75, 'UK 7', 15),
(143, 63, 'UK 8', 14),
(144, 70, 'UK 8', 15),
(145, 71, 'UK 8', 15),
(146, 72, 'UK 8', 15),
(147, 73, 'UK 8', 14),
(148, 75, 'UK 8', 15),
(149, 63, 'UK 9', 15),
(150, 70, 'UK 9', 15),
(151, 71, 'UK 9', 15),
(152, 72, 'UK 9', 15),
(153, 73, 'UK 9', 14),
(154, 75, 'UK 9', 15),
(159, 48, 'M', 15),
(160, 48, 'L', 15),
(161, 48, 'XL', 15),
(162, 48, 'XXL', 15),
(163, 49, 'S', 15),
(164, 49, 'M', 14),
(165, 49, 'L', 15),
(166, 49, 'XL', 15),
(167, 49, 'XXL', 15),
(168, 35, 'S', 12),
(169, 35, 'M', 15),
(170, 35, 'L', 15),
(171, 35, 'XL', 15),
(172, 35, 'XXL', 15),
(173, 36, 'UK 4', 15),
(174, 36, 'UK 5', 15),
(175, 36, 'UK 6', 14),
(176, 36, 'UK 7', 15),
(177, 36, 'UK 8', 15),
(178, 36, 'UK 9', 15),
(179, 103, '210 x 297 mm', 1),
(180, 104, '82 x 24 cm', 3),
(181, 105, '32 cm', 3),
(182, 106, '420 x 594 mm', 1);

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `reviewID` int(11) NOT NULL,
  `buyerID` int(11) NOT NULL,
  `productID` int(11) NOT NULL,
  `orderID` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `rComment` text NOT NULL,
  `reviewDate` datetime NOT NULL,
  `rStatus` enum('pending','accepted','rejected') NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`reviewID`, `buyerID`, `productID`, `orderID`, `rating`, `rComment`, `reviewDate`, `rStatus`) VALUES
(31, 1, 103, 501, 5, 'Beautiful artwork. The details and colors are just stunning. Worth every cent.', '2025-05-24 18:15:00', 'accepted'),
(32, 3, 105, 502, 4, 'Great craftsmanship, but a bit smaller than expected.', '2025-05-24 18:16:00', 'accepted'),
(33, 4, 54, 503, 5, 'Cleaned my white sneakers like magic. Highly recommend!', '2025-05-25 18:20:00', 'accepted'),
(34, 5, 35, 504, 5, 'Fits perfectly and feels premium. Got lots of compliments.', '2025-05-26 18:22:00', 'accepted'),
(35, 6, 51, 505, 5, 'Very relaxing scent. Perfect for my evening routine.', '2025-05-27 18:25:00', 'accepted'),
(36, 7, 75, 506, 4, 'Stylish and comfortable. Slightly tight on the toes though.', '2025-05-28 18:28:00', 'accepted'),
(37, 8, 43, 507, 5, 'Perfect for daily journaling. Love the African patterns.', '2025-05-28 18:30:00', 'accepted'),
(38, 9, 77, 508, 5, 'My kids love them! Sturdy and well-made.', '2025-05-28 18:31:00', 'accepted'),
(39, 10, 57, 509, 5, 'Left my skin soft and glowing. Will buy again.', '2025-05-29 18:33:00', 'accepted'),
(40, 10, 63, 510, 5, 'Stylish and durable. Great quality leather.', '2025-05-30 18:34:00', 'accepted'),
(41, 11, 57, 162, 5, 'Amazing feel on your face! A definite reccomend', '2025-05-31 14:11:31', 'accepted');

-- --------------------------------------------------------

--
-- Table structure for table `sellers`
--

CREATE TABLE `sellers` (
  `sellerID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `businessName` varchar(100) DEFAULT NULL,
  `payDetails` varchar(25) DEFAULT NULL,
  `businessDescript` varchar(244) DEFAULT NULL,
  `totalSales` decimal(10,2) DEFAULT 0.00,
  `rating` float DEFAULT 0,
  `imagePath` varchar(244) DEFAULT NULL,
  `pickupAddress` varchar(40) DEFAULT NULL,
  `city` varchar(40) DEFAULT NULL,
  `status` enum('accepted','rejected','resubmit','pending','inactive') NOT NULL DEFAULT 'pending',
  `postalcode` varchar(4) DEFAULT NULL,
  `poa` varchar(255) DEFAULT NULL,
  `giid` varchar(255) DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sellers`
--

INSERT INTO `sellers` (`sellerID`, `userID`, `businessName`, `payDetails`, `businessDescript`, `totalSales`, `rating`, `imagePath`, `pickupAddress`, `city`, `status`, `postalcode`, `poa`, `giid`, `createdAt`) VALUES
(1, 2, 'Jozi Blooms', '62013456789', 'Handcrafted designs from the heart of Johannesburg.', 12350.00, 4.6, 'joziblooms.png', '12 Rose Street, Newtown', 'Johannesburg', 'accepted', '0001', 'placeholder.png', 'placeholder.png', '2025-05-07 14:05:13'),
(2, 8, 'Zulu Threads', '7788996655', 'Modern fashion with traditional Zulu flair.', 14800.00, 4.7, 'zuluthreads.png', '45 Zulu Lane, Umlazi', 'Durban', 'accepted', '2004', 'placeholder.png', 'placeholder.png', '2025-05-07 14:05:13'),
(3, 11, 'Cape Clay Co.', '1019988776', 'Cape Town pottery studio making custom ceramics.', 9650.50, 4.5, 'placeholder.png', '89 Clay Road, Observatory', 'Cape Town', 'accepted', '1102', 'placeholder.png', 'placeholder.png', '2025-05-07 14:05:13'),
(5, 20, 'Ubuntu Prints', '5566778899', 'African print stationery and custom journals.', 8680.00, 4.8, 'placeholder.png', '7 Ubuntu Avenue, Windhoek CBD', 'Windhoek', 'accepted', '3025', 'placeholder.png', 'placeholder.png', '2025-05-07 14:05:13'),
(6, 1, 'Braai Boss Co.', '7099776655', 'Handmade wooden braai tools and spice kits.', 9400.10, 4.6, 'braaibossco.png', '33 Flamewood Drive, Randburg', 'Johannesburg', 'accepted', '0123', 'placeholder.png', 'placeholder.png', '2025-05-07 14:05:13'),
(7, 7, 'Maboneng Ink', '1122334455', 'Urban t-shirt printing studio with street art vibes.', 13750.00, 3, 'mabonengink.png', '21 Graffiti Street, Maboneng', 'Johannesburg', 'accepted', '0123', 'placeholder.png', 'placeholder.png', '2025-05-07 14:05:13'),
(8, 4, 'Soweto Scented', '8877665544', 'Soy-based candles with local inspired fragrances.', 7190.75, 4.5, 'placeholder.png', '5 Candle Way, Diepkloof', 'Soweto', 'accepted', '4002', 'placeholder.png', 'placeholder.png', '2025-05-07 14:05:13'),
(9, 9, 'Tekkie Fix', '3344556677', 'Sneaker cleaning and restoration by township youth.', 4900.00, 4.3, 'placeholder.png', '67 Sneaker Street, Mdantsane', 'East London', 'accepted', '5001', 'placeholder.png', 'placeholder.png', '2025-05-07 14:05:13'),
(10, 10, 'Mzansi Skincare', '6677889900', 'Natural skincare made with Rooibos and Marula oil.', 15450.00, 4.9, 'mzanziskin.png', '88 Natural Way, Stellenbosch', 'Cape Town', 'accepted', '', 'placeholder.png', 'placeholder.png', '2025-05-07 14:05:13'),
(18, 22, 'Society', '13579113', 'Society is a bold South African clothing brand born from the rhythm of the streets, the richness of culture, and the pulse of everyday people. ', 14949.96, 3.5, 's_681082f202cc99.97526830.png', '318 Mooiplaas Road', 'Pretoria', 'accepted', '0080', 'placeholder.png', 'placeholder.png', '2025-05-07 14:05:13'),
(20, 21, 'Zen Living', '25488136', 'Zen products for Zen people', 0.00, 0, 'biz_681cad3d8f38a5.30818043.png', '318 Mooiplaas Road', 'Pretoria', 'accepted', '0081', 'proof_681cad3d8f8431.74823484.png', 'id_681cad3d8f6b61.94696465.png', '2025-05-08 13:10:21'),
(28, 25, 'Tiny Toybox', '25488136', 'Tiny Toybox believes that every child deserves toys that spark imagination, creativity, and joy. Specializing in handcrafted toys, our collection is lovingly made with care, precision, and a touch of magic.                                      ', 450.00, 0, 'tinytoy.png', '321 Rose Street', 'Krugersdorp', 'accepted', '1005', 'proof_682d8b401855d6.67498492.png', 'id_682d8b401826f1.46510690.png', '2025-05-21 07:58:13'),
(31, 27, 'Muse & Medium', '13579113', 'Inspired by creativity. Muse & Medium offers a curated collection of contemporary art and premium tools for artists at all levels.', 0.00, 0, 'placeholder.png', '131 Griffiths Road', 'Equestria', 'accepted', '0183', 'proof_6835b9d588f6b1.13380887.png', 'id_6835b9d587e836.18365817.png', '2025-05-27 13:10:45');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `userID` int(11) NOT NULL,
  `uFirst` varchar(50) DEFAULT NULL,
  `uLast` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `uPassword` varchar(255) DEFAULT NULL,
  `regDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` enum('buyer','seller','admin') NOT NULL DEFAULT 'buyer',
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`userID`, `uFirst`, `uLast`, `email`, `uPassword`, `regDate`, `role`, `status`) VALUES
(1, 'Jane', 'Lowe', 'janelowe@yahoo.com', '$2y$10$hQ5knFgn8YRbkKcQDdsZnudXgFo4mLXhPH/Zdsjooh8S33YO6aOZ2', '2025-04-24 08:26:58', '', 'active'),
(2, 'Nathan', 'Brookes', 'nbrookes@gmail.com', '$2y$10$6NIyOlybWecyYOCFeaDFV.JV1wDu4.7dT6roZarZ.Myr3IMKve9dO', '2025-04-24 08:26:58', 'seller', 'active'),
(4, 'David', 'Black', 'david@example.com', '$2y$10$Y8b3b19e4E.wuGSlJI4x8eqHOOLFgDC1py0pJSyd9/laCAk4OqGzi', '2025-04-24 08:26:58', 'seller', 'active'),
(5, 'Ella', 'Brown', 'ella@example.com', '$2y$10$Dq7LEmvGMBXER9Gr0CVNjeBu6uhmraZy/AcEBzHdEaFnp/pm0eRo2', '2025-04-24 08:26:58', 'buyer', 'active'),
(6, 'Frank', 'Miller', 'frank@example.com', '$2y$10$gDlHk4MCqvT6OTB8L9sZ2u0TFosLSvEolxS8R7nHoGHIXpoKP0E8W', '2025-04-24 08:26:58', 'buyer', 'active'),
(7, 'Grace', 'Wilson', 'grace@example.com', '$2y$10$SENtNUzEjZ9Gbf4PjQOjH.bu7eUCMt4E9Ow37tOqtixqnlVVcTfTq', '2025-04-24 08:26:58', 'seller', 'active'),
(8, 'Henry', 'Moore', 'henry@example.com', '$2y$10$EwDY2uzld7HAYVkoP7YlW.0bhUMQgB5HzdT3eyf/9gL6TmHfLPHx6', '2025-04-24 08:26:58', 'seller', 'active'),
(9, 'Isla', 'Taylor', 'isla@example.com', '$2y$10$lrvRTu/FQ4k4OiIHcE081O.e9AYFp5mHpUSXUndOPRwYCTOv7uPd6', '2025-04-24 08:26:58', 'seller', 'active'),
(10, 'Jack', 'Anderson', 'jack@example.com', '$2y$10$UnWPYK.BPyQm1iApLfWfQ.z0nYjJBrtwH/Qj8S4ydJ.EuJQ156pta', '2025-04-24 08:26:58', 'seller', 'active'),
(11, 'Katie', 'Thomas', 'katie@example.com', '$2y$10$mpdKhLVI3QtNuiDEo7MDp.nbWa7j6M4hpG5NXXMFESz8yEDChVame', '2025-04-24 08:26:58', 'buyer', 'active'),
(12, 'Leo', 'Jackson', 'leo@example.com', '$2y$10$hMQaNBBxBZW2QbVfM7EsfuHo7FRIQW7zAupoxhRCY81lB9IYiRYZK', '2025-04-24 08:26:58', 'buyer', 'active'),
(13, 'Mia', 'Martin', 'mia@example.com', '$2y$10$9GpXIbvO7AWVk0JDJWk9A.pvqECsDAAk5RH8g1Mv3LYGfJvjPvCKC', '2025-04-24 08:26:58', 'buyer', 'active'),
(14, 'Noah', 'Lee', 'noah.lee@example.com', '$2y$10$0GCKc8FQKjNVJ9UqK1S1QezTR5kQIlKf9Szn8yQRM5UKs4LGaWFOO', '2025-04-24 08:26:58', 'buyer', 'active'),
(15, 'Olivia', 'Perez', 'olivia@example.com', '$2y$10$KmDo.CvbPDAv7aS9zYYaaOmx4Vn1SvPRqG3B8GxyUET9KvBaF7Y.a', '2025-04-24 08:26:58', 'buyer', 'active'),
(16, 'Paul', 'Young', 'paul@example.com', '$2y$10$0tQIQZLdPbYEFvLAVglPf.cXUIaTDRYbt0u6zZ8EExILv5A3EN0ae', '2025-04-24 08:26:58', 'buyer', 'active'),
(17, 'Quinn', 'King', 'quinn@example.com', '$2y$10$ESu2OaHLE0e5ZKBeI9LzUO3jNVgx2q.RKug3YMPGiEKKKRXZAzfxG', '2025-04-24 08:26:58', 'buyer', 'active'),
(18, 'Riley', 'Scott', 'riley@example.com', '$2y$10$7FIO1dRQ2e7U2ehKjH6HYugZZ7J84PlKnZB3zTuEpKZ6ncmhA3j6S', '2025-04-24 08:26:58', 'buyer', 'active'),
(19, 'Sophie', 'Adams', 'sophie@example.com', '$2y$10$kzBSrU49p6/csNiMKYzyAu0GhKhTFtGZrKmXoL9aP.fq3ChjNjC7i', '2025-04-24 08:26:58', 'buyer', 'active'),
(20, 'Tania', 'Muller', 'taniamuller@example.com', '$2y$10$X0fwrNPuDlIdYoOZ6CZrYeNz7YJPdkGrG0rC5EIj3mCJo7NK1yHwm', '2025-04-24 08:26:58', 'seller', 'active'),
(21, 'Tanel', 'Minnie', 'tminnie80@yahoo.com', '$2y$10$Cgx3XnpW0HMRDBleWjdJi.dVylDoOnG48h2DfRhTf4WVEb08alw/G', '2025-04-24 09:30:29', 'buyer', 'active'),
(22, 'Kelley', 'Scott', 'kelleyscott@gmail.com', '$2y$10$Lp9JJoeQlkZhwq7ktC.zdezDi8LTHKSVZVNK4Ih3Qcccyavbe59ge', '2025-04-24 12:40:08', 'seller', 'active'),
(23, 'admin', 'admin', 'admin@tukocart.com', '$2y$10$GrnCCKGMr3S1sAkwi4DiWei/UQPwewCeSkHB58GE959rP8c4qHIf2', '2025-05-06 06:54:55', 'admin', 'active'),
(24, 'Tristan', 'Botes', 'tristan.botes@seeff.com', '$2y$10$k9JRd5qLPEOCIhjKV8c0oe0ZpWeyjlD4iEsVQTQ/8ySq97txUMQ9K', '2025-05-07 08:34:36', 'buyer', 'active'),
(25, 'Beth', 'Lukas', 'blukas@gmail.com', '$2y$10$lUxJ6Qwx84zvPER.2/c0oeXM5jrL.qi8muRhpFLHN9UvsmIiK3F/y', '2025-05-21 07:25:37', 'seller', 'active'),
(27, 'Kate', 'Winslet', 'katewin@gmail.com', '$2y$10$cFGJ6sCL1AkRlchE4dSzVu6b1LY8/jCBLcg/jwZTgZ3pi4DhW8Pq2', '2025-05-24 12:20:54', 'seller', 'active'),
(28, 'Chane', 'Muller', 'chanemuller@gmail.com', '$2y$10$1VToxfgKrmPbmIUb5Nnrxu2CfiZJd/yC80jX8g1FBdSCEgO/LjYZC', '2025-06-02 12:14:54', 'buyer', 'active'),
(32, 'j', 's', 'sdas@gmail.com', '$2y$10$ULRu5uHDb3vcXsdSjn3.WuVQwlRm1gIUFqZI4yInK0fexEQrMUFQO', '2025-06-18 07:17:19', 'buyer', 'active'),
(33, 'John', 'Doe', 'johndoe@gmail.com', '$2y$10$OmhfmXGnF2cxbG/m34ABpObDwft.1hhA9J7zYy03dZCBjAqZ5I80i', '2025-06-18 07:18:17', 'buyer', 'active');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `buyers`
--
ALTER TABLE `buyers`
  ADD PRIMARY KEY (`buyerID`),
  ADD KEY `userID` (`userID`);

--
-- Indexes for table `cartitems`
--
ALTER TABLE `cartitems`
  ADD PRIMARY KEY (`cartItemID`),
  ADD KEY `fk_cartitems_cart` (`cartID`),
  ADD KEY `fk_cartitems_product` (`productID`);

--
-- Indexes for table `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`cartID`),
  ADD KEY `buyerID` (`buyerID`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`categoryID`),
  ADD KEY `parentCategoryID` (`parentID`);

--
-- Indexes for table `deliveries`
--
ALTER TABLE `deliveries`
  ADD PRIMARY KEY (`deliveryID`),
  ADD KEY `fk_deliveries_order_new` (`orderID`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`feedbackID`),
  ADD KEY `userID` (`userID`);

--
-- Indexes for table `orderitems`
--
ALTER TABLE `orderitems`
  ADD PRIMARY KEY (`orderItemID`),
  ADD KEY `fk_orderitems_product_new` (`productID`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`orderID`),
  ADD KEY `buyerID` (`buyerID`);

--
-- Indexes for table `paymentrecords`
--
ALTER TABLE `paymentrecords`
  ADD PRIMARY KEY (`paymentID`),
  ADD KEY `fk_paymentrecords_order_new` (`orderID`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`productID`),
  ADD KEY `sellerID` (`sellerID`),
  ADD KEY `categoryID` (`pCategory`);

--
-- Indexes for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`variantID`),
  ADD KEY `productID` (`productID`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`reviewID`),
  ADD UNIQUE KEY `buyerID` (`buyerID`,`productID`,`orderID`);

--
-- Indexes for table `sellers`
--
ALTER TABLE `sellers`
  ADD PRIMARY KEY (`sellerID`),
  ADD KEY `userID` (`userID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`userID`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `buyers`
--
ALTER TABLE `buyers`
  MODIFY `buyerID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `cartitems`
--
ALTER TABLE `cartitems`
  MODIFY `cartItemID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `carts`
--
ALTER TABLE `carts`
  MODIFY `cartID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `categoryID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `deliveries`
--
ALTER TABLE `deliveries`
  MODIFY `deliveryID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `feedbackID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `orderitems`
--
ALTER TABLE `orderitems`
  MODIFY `orderItemID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `orderID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=196;

--
-- AUTO_INCREMENT for table `paymentrecords`
--
ALTER TABLE `paymentrecords`
  MODIFY `paymentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=114;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `productID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=107;

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `variantID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=183;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `reviewID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `sellers`
--
ALTER TABLE `sellers`
  MODIFY `sellerID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `userID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `buyers`
--
ALTER TABLE `buyers`
  ADD CONSTRAINT `buyers_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `users` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `cartitems`
--
ALTER TABLE `cartitems`
  ADD CONSTRAINT `cartitems_ibfk_1` FOREIGN KEY (`cartID`) REFERENCES `carts` (`cartID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_cartitems_cart` FOREIGN KEY (`cartID`) REFERENCES `carts` (`cartID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cartitems_product` FOREIGN KEY (`productID`) REFERENCES `products` (`productID`) ON DELETE SET NULL;

--
-- Constraints for table `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `carts_ibfk_1` FOREIGN KEY (`buyerID`) REFERENCES `buyers` (`buyerID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parentID`) REFERENCES `categories` (`categoryID`) ON DELETE CASCADE;

--
-- Constraints for table `deliveries`
--
ALTER TABLE `deliveries`
  ADD CONSTRAINT `deliveries_ibfk_1` FOREIGN KEY (`orderID`) REFERENCES `orders` (`orderID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_deliveries_order` FOREIGN KEY (`orderID`) REFERENCES `orders` (`orderID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_deliveries_order_new` FOREIGN KEY (`orderID`) REFERENCES `orders` (`orderID`) ON DELETE CASCADE;

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `users` (`userID`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`buyerID`) REFERENCES `buyers` (`buyerID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `paymentrecords`
--
ALTER TABLE `paymentrecords`
  ADD CONSTRAINT `fk_paymentrecords_order` FOREIGN KEY (`orderID`) REFERENCES `orders` (`orderID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_paymentrecords_order_new` FOREIGN KEY (`orderID`) REFERENCES `orders` (`orderID`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`sellerID`) REFERENCES `sellers` (`sellerID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`pCategory`) REFERENCES `categories` (`categoryID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `product_variants_ibfk_1` FOREIGN KEY (`productID`) REFERENCES `products` (`productID`);

--
-- Constraints for table `sellers`
--
ALTER TABLE `sellers`
  ADD CONSTRAINT `sellers_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `users` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
