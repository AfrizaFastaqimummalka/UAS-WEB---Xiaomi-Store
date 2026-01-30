-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 30, 2026 at 10:46 AM
-- Server version: 10.4.32-MariaDB-log
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `xiaomi_store`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password`, `email`, `created_at`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@xiaomisurabaya.com', '2026-01-30 06:29:01');

-- --------------------------------------------------------

--
-- Stand-in structure for view `flagship_products`
-- (See below for the actual view)
--
CREATE TABLE `flagship_products` (
`id` int(11)
,`nama` varchar(100)
,`harga` int(11)
,`deskripsi` text
,`gambar` varchar(255)
,`kategori` varchar(50)
,`created_at` timestamp
);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `nama_pembeli` varchar(100) NOT NULL,
  `telepon` varchar(20) NOT NULL,
  `alamat` text NOT NULL,
  `total_harga` int(11) NOT NULL,
  `status` enum('pending','processing','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `nama_pembeli`, `telepon`, `alamat`, `total_harga`, `status`, `created_at`) VALUES
(1, 'Afriza Fastaqimummalka', '0821123125456', 'Miawzono', 8999000, 'pending', '2026-01-30 07:43:50');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `nama_produk` varchar(100) NOT NULL,
  `harga` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `subtotal` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `nama_produk`, `harga`, `quantity`, `subtotal`) VALUES
(1, 1, 8, 'Xiaomi 14 Pro', 8999000, 1, 8999000);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `harga` int(11) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `kategori` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `nama`, `harga`, `deskripsi`, `gambar`, `kategori`, `created_at`) VALUES
(7, 'Xiaomi 13T Pro 512GB', 6999000, 'Flagship killer dengan MediaTek Dimensity 9200+ dan kamera Leica 50MP', 'images/xiaomi-13t-pro.jpg', 'Smartphone', '2026-01-30 07:32:07'),
(8, 'Xiaomi 14 Pro', 8999000, 'Flagship terbaru dengan Snapdragon 8 Gen 3 dan kamera Leica 50MP', 'images/xiaomi-14-pro.jpg', 'Smartphone', '2026-01-30 07:32:07'),
(9, 'Redmi Note 13 Pro', 4299000, 'Smartphone dengan kamera 200MP dan Snapdragon 7s Gen 2', 'images/redmi-note-13-pro.jpg', 'Smartphone', '2026-01-30 07:32:07'),
(10, 'Mi Band 8', 549000, 'Smartband dengan layar AMOLED 1.62 inch dan baterai 16 hari', 'images/xiaomi-mi-band-8.jpg', 'Wearable', '2026-01-30 07:32:07'),
(11, 'Poco X6', 3999000, 'Gaming phone dengan Snapdragon 7s Gen 2 dan layar 120Hz', 'images/poco-x6.jpg', 'Smartphone', '2026-01-30 07:32:07'),
(12, 'Xiaomi Pad 6', 5499000, 'Tablet 11 inch dengan layar 144Hz dan Snapdragon 870', 'images/xiaomi-pad-6.jpg', 'Tablet', '2026-01-30 07:32:08'),
(13, 'Redmi Buds 5', 399000, 'TWS Earbuds dengan Active Noise Cancellation 46dB', 'images/redmi-buds-5.jpg', 'Audio', '2026-01-30 07:32:08');

-- --------------------------------------------------------

--
-- Structure for view `flagship_products`
--
DROP TABLE IF EXISTS `flagship_products`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `flagship_products`  AS SELECT `products`.`id` AS `id`, `products`.`nama` AS `nama`, `products`.`harga` AS `harga`, `products`.`deskripsi` AS `deskripsi`, `products`.`gambar` AS `gambar`, `products`.`kategori` AS `kategori`, `products`.`created_at` AS `created_at` FROM `products` WHERE `products`.`kategori` in ('Flagship','Best Seller') ORDER BY `products`.`harga` DESC ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_kategori` (`kategori`),
  ADD KEY `idx_harga` (`harga`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
