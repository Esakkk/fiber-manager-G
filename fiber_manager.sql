-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 17 Jun 2026 pada 11.14
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `fiber_manager`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `login_logs`
--

CREATE TABLE `login_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `login_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `status` enum('success','failed') DEFAULT 'success'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `login_logs`
--

INSERT INTO `login_logs` (`id`, `user_id`, `login_time`, `ip_address`, `user_agent`, `status`) VALUES
(1, 4, '2026-05-22 11:01:03', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'success'),
(2, 4, '2026-05-23 15:05:17', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'failed'),
(3, 4, '2026-05-23 15:05:21', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'success'),
(4, 3, '2026-05-26 08:38:27', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'success'),
(5, 1, '2026-05-29 02:52:38', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'success'),
(6, 3, '2026-05-29 09:06:58', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'success'),
(7, 4, '2026-05-29 15:53:57', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'success'),
(8, 4, '2026-05-30 08:20:34', '192.168.101.6', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36', 'failed'),
(9, 4, '2026-05-30 08:20:39', '192.168.101.6', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36', 'success'),
(10, 1, '2026-06-02 09:41:22', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'success'),
(11, 1, '2026-06-02 10:31:45', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456', 'success'),
(12, 1, '2026-06-02 10:32:40', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456', 'success'),
(13, 1, '2026-06-03 06:20:31', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'success'),
(14, 4, '2026-06-06 04:22:14', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'failed'),
(15, 1, '2026-06-06 04:23:36', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'success'),
(16, 3, '2026-06-06 09:37:50', '192.168.101.3', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36', 'success'),
(17, 1, '2026-06-06 09:40:01', '192.168.101.3', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36', 'failed'),
(18, 4, '2026-06-06 09:40:18', '192.168.101.3', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36', 'success'),
(19, 1, '2026-06-06 09:49:27', '192.168.101.6', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36', 'success'),
(20, 1, '2026-06-14 01:01:24', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'success'),
(21, 1, '2026-06-14 01:40:23', '192.168.101.49', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36', 'success'),
(22, 3, '2026-06-14 03:06:47', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'success'),
(23, 1, '2026-06-14 04:56:02', '192.168.101.49', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36', 'success'),
(24, 3, '2026-06-14 07:36:35', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'success'),
(25, 1, '2026-06-14 07:55:31', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'success'),
(26, 1, '2026-06-14 08:40:36', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'success'),
(27, 1, '2026-06-14 12:26:27', '192.168.101.3', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36', 'success'),
(28, 1, '2026-06-14 12:45:53', '192.168.101.3', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36', 'success'),
(29, 1, '2026-06-15 03:36:27', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'success'),
(30, 1, '2026-06-15 03:51:58', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'success'),
(31, 1, '2026-06-15 08:27:32', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'success'),
(32, 3, '2026-06-15 08:28:15', '::1', 'Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36', 'success'),
(33, 3, '2026-06-15 09:51:10', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'success'),
(34, 1, '2026-06-16 04:30:37', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'failed'),
(35, 1, '2026-06-16 04:30:40', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'failed'),
(36, 1, '2026-06-16 04:30:41', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'failed'),
(37, 1, '2026-06-16 04:30:41', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'failed'),
(38, 1, '2026-06-16 04:30:41', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'failed'),
(39, 1, '2026-06-16 04:30:42', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'failed'),
(40, 1, '2026-06-16 04:30:42', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'failed'),
(41, 1, '2026-06-16 04:30:42', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'failed'),
(42, 1, '2026-06-16 04:30:42', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'failed'),
(43, 1, '2026-06-16 04:30:42', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'failed'),
(44, 1, '2026-06-16 04:30:43', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'failed'),
(45, 1, '2026-06-16 04:30:43', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'failed'),
(46, 1, '2026-06-16 04:30:43', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'failed'),
(47, 1, '2026-06-16 04:30:43', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'failed'),
(48, 1, '2026-06-16 04:30:43', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'failed'),
(49, 1, '2026-06-16 04:30:44', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'failed'),
(50, 1, '2026-06-16 04:30:44', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'failed'),
(51, 1, '2026-06-16 04:30:44', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'failed'),
(52, 1, '2026-06-16 04:30:44', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'failed'),
(53, 1, '2026-06-16 04:30:44', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'failed'),
(54, 1, '2026-06-16 04:30:45', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'failed'),
(55, 1, '2026-06-16 04:30:45', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'failed'),
(56, 1, '2026-06-16 04:30:45', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'failed'),
(57, 1, '2026-06-16 04:30:45', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'failed'),
(58, 1, '2026-06-16 04:30:45', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'failed'),
(59, 1, '2026-06-16 04:30:46', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'failed'),
(60, 1, '2026-06-16 04:30:46', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'failed'),
(61, 1, '2026-06-16 04:30:46', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'failed'),
(62, 1, '2026-06-16 04:30:46', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'failed'),
(63, 1, '2026-06-16 04:34:46', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'failed'),
(64, 1, '2026-06-16 04:34:53', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'failed'),
(65, 1, '2026-06-16 04:34:56', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'success'),
(66, 3, '2026-06-16 04:45:37', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'success'),
(67, 1, '2026-06-16 06:56:05', '192.168.101.49', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36', 'failed'),
(68, 1, '2026-06-16 06:56:10', '192.168.101.49', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36', 'failed'),
(69, 1, '2026-06-16 06:56:16', '192.168.101.49', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36', 'success'),
(70, 1, '2026-06-16 07:43:59', '192.168.101.23', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'success'),
(71, 3, '2026-06-16 08:05:32', '192.168.101.49', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36', 'success'),
(72, 1, '2026-06-17 03:35:25', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'success'),
(73, 1, '2026-06-17 03:35:48', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'failed'),
(74, 1, '2026-06-17 03:35:52', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'success'),
(75, 1, '2026-06-17 06:38:36', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'success');

-- --------------------------------------------------------

--
-- Struktur dari tabel `odc`
--

CREATE TABLE `odc` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `lat` decimal(10,8) NOT NULL,
  `lng` decimal(11,8) NOT NULL,
  `location` varchar(255) NOT NULL,
  `capacity` int(11) NOT NULL DEFAULT 24,
  `used_ports` int(11) NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL,
  `source_type` enum('pop','olt','pon') DEFAULT NULL,
  `source_id` int(11) DEFAULT NULL,
  `pon_id` int(11) DEFAULT NULL,
  `pon_port_number` int(11) DEFAULT NULL,
  `olt_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `path_coordinates` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `odc`
--

INSERT INTO `odc` (`id`, `name`, `lat`, `lng`, `location`, `capacity`, `used_ports`, `description`, `source_type`, `source_id`, `pon_id`, `pon_port_number`, `olt_id`, `created_at`, `updated_at`, `path_coordinates`) VALUES
(12, 'MS 1', -6.96172473, 109.64688526, 'lampu merah podo', 8, 8, '', 'pon', 5, 10, 1, 6, '2026-06-14 12:48:50', '2026-06-15 09:00:31', '[[-6.9617693,109.6469443],[-6.961687118760083,109.64694131165744],[-6.96172473,109.64688526]]'),
(13, 'MS2 Pekajangan', -6.95417866, 109.64993834, 'pekajangan', 16, 0, 'pekajangan', 'pon', 5, 10, 2, 6, '2026-06-16 08:02:16', '2026-06-16 08:03:33', '[[-6.9617693,109.6469443],[-6.961489765782033,109.64697718620302],[-6.961532364745541,109.64712738990785],[-6.960802856961672,109.6474036574364],[-6.95964203201878,109.64829415082933],[-6.958292170046093,109.64906930923463],[-6.957674480432785,109.64927315711977],[-6.957184588092675,109.64944481849672],[-6.955895955801034,109.65012073516847],[-6.955203713853808,109.65035676956178],[-6.954745768620296,109.65024948120119],[-6.95417866,109.64993834]]');

-- --------------------------------------------------------

--
-- Struktur dari tabel `odc_odp_connections`
--

CREATE TABLE `odc_odp_connections` (
  `id` int(11) NOT NULL,
  `odc_id` int(11) NOT NULL,
  `odp_id` int(11) NOT NULL,
  `port_number` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `odc_odp_connections`
--

INSERT INTO `odc_odp_connections` (`id`, `odc_id`, `odp_id`, `port_number`, `created_at`) VALUES
(32, 12, 11, 1, '2026-06-14 12:50:28'),
(33, 12, 13, 2, '2026-06-14 12:51:23'),
(43, 12, 14, 3, '2026-06-15 04:09:22'),
(44, 12, 15, 4, '2026-06-15 04:13:12'),
(45, 12, 16, 5, '2026-06-15 04:13:31'),
(46, 12, 18, 6, '2026-06-15 04:13:39'),
(47, 12, 19, 7, '2026-06-15 04:13:45'),
(48, 12, 20, 8, '2026-06-15 04:13:51');

-- --------------------------------------------------------

--
-- Struktur dari tabel `odc_photos`
--

CREATE TABLE `odc_photos` (
  `id` int(11) NOT NULL,
  `odc_id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `file_size` int(11) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `odc_photos`
--

INSERT INTO `odc_photos` (`id`, `odc_id`, `filename`, `original_name`, `file_size`, `is_primary`, `created_at`) VALUES
(2, 12, 'odc_12_1781441337_140db83dec1c4999.jpg', '44359.jpg', 5040979, 1, '2026-06-14 12:48:57'),
(3, 12, 'odc_12_1781441365_08871acb4f2094ca.jpg', '44359.jpg', 5040979, 0, '2026-06-14 12:49:25');

-- --------------------------------------------------------

--
-- Struktur dari tabel `odp`
--

CREATE TABLE `odp` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `source_id` int(11) DEFAULT NULL,
  `port_number_in_odc` int(11) DEFAULT NULL,
  `source_type` enum('odc','odp') DEFAULT NULL,
  `lat` decimal(10,8) NOT NULL,
  `lng` decimal(11,8) NOT NULL,
  `path_coordinates` longtext DEFAULT NULL,
  `location` varchar(255) NOT NULL,
  `total_ports` int(11) NOT NULL DEFAULT 8,
  `available_ports` int(11) NOT NULL DEFAULT 8,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `odp`
--

INSERT INTO `odp` (`id`, `name`, `source_id`, `port_number_in_odc`, `source_type`, `lat`, `lng`, `path_coordinates`, `location`, `total_ports`, `available_ports`, `description`, `created_at`, `updated_at`) VALUES
(11, 'ODP 1', 12, 1, 'odc', -6.96172170, 109.64689819, NULL, 'lampu merah podo', 8, 8, '', '2026-06-14 12:50:00', '2026-06-14 12:50:28'),
(13, 'ODP 2', 12, 2, 'odc', -6.96177997, 109.64624079, NULL, 'podo', 8, 8, '', '2026-06-14 12:51:23', '2026-06-14 12:51:23'),
(14, 'ODP 3', 12, 3, 'odc', -6.96204417, 109.64466725, '[[-6.96204417,109.64466725],[-6.961813763648869,109.6459971646048],[-6.96172473,109.64688526]]', '', 8, 8, '', '2026-06-14 12:51:43', '2026-06-14 12:55:35'),
(15, 'ODP 4', 12, 4, 'odc', -6.96339526, 109.64353172, '[[-6.96339526,109.64353172],[-6.9626708617414925,109.64427054541147],[-6.962412401096489,109.64457291154629],[-6.962212514125016,109.64465583576902],[-6.96204417,109.64466725],[-6.961824560574975,109.64597521453842],[-6.96177997,109.64624079],[-6.96172473,109.64688526]]', 'test', 8, 8, '', '2026-06-14 12:52:06', '2026-06-15 04:13:12'),
(16, 'ODP 5', 12, 5, 'odc', -6.96528741, 109.64236491, '[[-6.96528741,109.64236491],[-6.964405123421234,109.64286804199219],[-6.96339526,109.64353172],[-6.9626708617414925,109.64427054541147],[-6.962412401096489,109.64457291154629],[-6.962212514125016,109.64465583576902],[-6.96204417,109.64466725],[-6.96177997,109.64624079],[-6.96172473,109.64688526]]', 'test', 8, 8, '', '2026-06-14 12:52:22', '2026-06-15 04:13:31'),
(18, 'ODP6', 12, 6, 'odc', -6.96819148, 109.64149213, '[[-6.96819148,109.64149213],[-6.966290112139906,109.64237451553346],[-6.96528741,109.64236491],[-6.9643192048063645,109.64292450740139],[-6.96339526,109.64353172],[-6.9626708617414925,109.64427054541147],[-6.962412401096489,109.64457291154629],[-6.962212514125016,109.64465583576902],[-6.96204417,109.64466725],[-6.961803274211552,109.64610199325647],[-6.96172473,109.64688526]]', '', 8, 7, 'test', '2026-06-14 12:52:49', '2026-06-16 04:48:30'),
(19, 'ODP 7', 12, 7, 'odc', -6.96153298, 109.64101453, '[[-6.96153298,109.64101453],[-6.961618177081426,109.64102822852142],[-6.961756214318074,109.64197226880643],[-6.961965112950755,109.64335795552861],[-6.962080211635297,109.64428460385963],[-6.96204417,109.64466725],[-6.961813763648869,109.6459971646048],[-6.96172473,109.64688526]]', '', 8, 7, 'test', '2026-06-14 12:54:29', '2026-06-17 07:45:17'),
(20, 'ODP 8', 12, 8, 'odc', -6.95970081, 109.64134963, '[[-6.95970081,109.64134963],[-6.961618177081426,109.64102822852142],[-6.962080211635297,109.64428460385963],[-6.96204417,109.64466725],[-6.96177997,109.64624079],[-6.96172473,109.64688526]]', '', 8, 8, 'test', '2026-06-14 12:54:46', '2026-06-15 04:13:51');

-- --------------------------------------------------------

--
-- Struktur dari tabel `odp_photos`
--

CREATE TABLE `odp_photos` (
  `id` int(11) NOT NULL,
  `odp_id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `file_size` int(11) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `odp_ports`
--

CREATE TABLE `odp_ports` (
  `id` int(11) NOT NULL,
  `odp_id` int(11) NOT NULL,
  `port_number` int(11) NOT NULL,
  `status` enum('available','used','maintenance') DEFAULT 'available',
  `target` varchar(255) DEFAULT NULL,
  `connection_type` enum('feeder','distribusi','drop') DEFAULT NULL,
  `target_port` int(11) DEFAULT NULL,
  `lat` decimal(10,8) DEFAULT NULL,
  `lng` decimal(11,8) DEFAULT NULL,
  `onu_number` varchar(50) DEFAULT NULL,
  `modem_type` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `has_photo` tinyint(1) DEFAULT 0,
  `path_coordinates` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `odp_ports`
--

INSERT INTO `odp_ports` (`id`, `odp_id`, `port_number`, `status`, `target`, `connection_type`, `target_port`, `lat`, `lng`, `onu_number`, `modem_type`, `description`, `has_photo`, `path_coordinates`, `created_at`, `updated_at`) VALUES
(89, 11, 1, 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-06-14 12:50:00', '2026-06-14 12:50:00'),
(90, 11, 2, 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-06-14 12:50:00', '2026-06-14 12:50:00'),
(91, 11, 3, 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-06-14 12:50:00', '2026-06-14 12:50:00'),
(92, 11, 4, 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-06-14 12:50:00', '2026-06-14 12:50:00'),
(93, 11, 5, 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-06-14 12:50:00', '2026-06-14 12:50:00'),
(94, 11, 6, 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-06-14 12:50:00', '2026-06-14 12:50:00'),
(95, 11, 7, 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-06-14 12:50:00', '2026-06-14 12:50:00'),
(96, 11, 8, 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-06-14 12:50:00', '2026-06-14 12:50:00'),
(105, 13, 1, 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-06-14 12:51:23', '2026-06-14 12:51:23'),
(106, 13, 2, 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-06-14 12:51:23', '2026-06-14 12:51:23'),
(107, 13, 3, 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-06-14 12:51:23', '2026-06-14 12:51:23'),
(108, 13, 4, 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-06-14 12:51:23', '2026-06-14 12:51:23'),
(109, 13, 5, 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-06-14 12:51:23', '2026-06-14 12:51:23'),
(110, 13, 6, 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-06-14 12:51:23', '2026-06-14 12:51:23'),
(111, 13, 7, 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-06-14 12:51:23', '2026-06-14 12:51:23'),
(112, 13, 8, 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-06-14 12:51:23', '2026-06-14 12:51:23'),
(113, 14, 1, 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-06-14 12:51:43', '2026-06-14 12:51:43'),
(114, 14, 2, 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-06-14 12:51:43', '2026-06-14 12:51:43'),
(115, 14, 3, 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-06-14 12:51:43', '2026-06-14 12:51:43'),
(116, 14, 4, 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-06-14 12:51:43', '2026-06-14 12:51:43'),
(117, 14, 5, 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-06-14 12:51:43', '2026-06-14 12:51:43'),
(118, 14, 6, 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-06-14 12:51:43', '2026-06-14 12:51:43'),
(119, 14, 7, 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-06-14 12:51:43', '2026-06-14 12:51:43'),
(120, 14, 8, 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-06-14 12:51:43', '2026-06-14 12:51:43'),
(121, 15, 1, 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-06-14 12:52:06', '2026-06-14 12:52:06'),
(122, 15, 2, 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-06-14 12:52:06', '2026-06-14 12:52:06'),
(123, 15, 3, 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-06-14 12:52:06', '2026-06-14 12:52:06'),
(124, 15, 4, 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-06-14 12:52:06', '2026-06-14 12:52:06'),
(125, 15, 5, 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-06-14 12:52:06', '2026-06-14 12:52:06'),
(126, 15, 6, 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-06-14 12:52:06', '2026-06-14 12:52:06'),
(127, 15, 7, 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-06-14 12:52:06', '2026-06-14 12:52:06'),
(128, 15, 8, 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-06-14 12:52:06', '2026-06-14 12:52:06'),
(129, 16, 1, 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-06-14 12:52:22', '2026-06-14 12:52:22'),
(130, 16, 2, 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-06-14 12:52:22', '2026-06-14 12:52:22'),
(131, 16, 3, 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-06-14 12:52:22', '2026-06-14 12:52:22'),
(132, 16, 4, 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-06-14 12:52:22', '2026-06-14 12:52:22'),
(133, 16, 5, 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-06-14 12:52:22', '2026-06-14 12:52:22'),
(134, 16, 6, 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-06-14 12:52:22', '2026-06-14 12:52:22'),
(135, 16, 7, 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-06-14 12:52:22', '2026-06-14 12:52:22'),
(136, 16, 8, 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-06-14 12:52:22', '2026-06-14 12:52:22'),
(145, 18, 1, 'used', 'pelanggan 1', 'drop', NULL, -6.96858737, 109.64284122, '', '', '', 0, '[[-6.96819148,109.64149213],[-6.968301557140141,109.64163154363634],[-6.9684772752875235,109.6421290934086],[-6.968589095892482,109.64243084192277],[-6.968730202808276,109.64270040392879],[-6.96858737,109.64284122]]', '2026-06-14 12:52:49', '2026-06-16 04:49:11'),
(146, 18, 2, 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-06-14 12:52:49', '2026-06-14 12:52:49'),
(147, 18, 3, 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-06-14 12:52:49', '2026-06-14 12:52:49'),
(148, 18, 4, 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-06-14 12:52:49', '2026-06-14 12:52:49'),
(149, 18, 5, 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-06-14 12:52:49', '2026-06-14 12:52:49'),
(150, 18, 6, 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-06-14 12:52:49', '2026-06-14 12:52:49'),
(151, 18, 7, 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-06-14 12:52:49', '2026-06-14 12:52:49'),
(152, 18, 8, 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-06-14 12:52:49', '2026-06-14 12:52:49'),
(153, 19, 1, 'used', 'pelanggan1', 'drop', NULL, -6.96056311, 109.63985592, '', '', '', 0, '[[-6.96153298,109.64101453],[-6.96142054,109.63994443],[-6.96056311,109.63985592]]', '2026-06-14 12:54:29', '2026-06-17 07:45:32'),
(154, 19, 2, 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-06-14 12:54:29', '2026-06-14 12:54:29'),
(155, 19, 3, 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-06-14 12:54:29', '2026-06-14 12:54:29'),
(156, 19, 4, 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-06-14 12:54:29', '2026-06-14 12:54:29'),
(157, 19, 5, 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-06-14 12:54:29', '2026-06-14 12:54:29'),
(158, 19, 6, 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-06-14 12:54:29', '2026-06-14 12:54:29'),
(159, 19, 7, 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-06-14 12:54:29', '2026-06-14 12:54:29'),
(160, 19, 8, 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-06-14 12:54:29', '2026-06-14 12:54:29'),
(161, 20, 1, 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-06-14 12:54:46', '2026-06-14 12:54:46'),
(162, 20, 2, 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-06-14 12:54:46', '2026-06-14 12:54:46'),
(163, 20, 3, 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-06-14 12:54:46', '2026-06-14 12:54:46'),
(164, 20, 4, 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-06-14 12:54:46', '2026-06-14 12:54:46'),
(165, 20, 5, 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-06-14 12:54:46', '2026-06-14 12:54:46'),
(166, 20, 6, 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-06-14 12:54:46', '2026-06-14 12:54:46'),
(167, 20, 7, 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-06-14 12:54:46', '2026-06-14 12:54:46'),
(168, 20, 8, 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2026-06-14 12:54:46', '2026-06-14 12:54:46');

-- --------------------------------------------------------

--
-- Struktur dari tabel `olt`
--

CREATE TABLE `olt` (
  `id` int(11) NOT NULL,
  `pop_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `model` varchar(100) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `management_port` int(11) DEFAULT 22,
  `total_ports` int(11) DEFAULT 16,
  `total_pon_ports` int(11) DEFAULT 16,
  `used_pon_ports` int(11) DEFAULT 0,
  `lat` decimal(10,8) DEFAULT NULL,
  `lng` decimal(11,8) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `has_photo` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `olt`
--

INSERT INTO `olt` (`id`, `pop_id`, `name`, `model`, `ip_address`, `management_port`, `total_ports`, `total_pon_ports`, `used_pon_ports`, `lat`, `lng`, `location`, `description`, `created_at`, `updated_at`, `has_photo`) VALUES
(6, 5, 'olt 1', 'hsgq', '192.168.1.100', 80, 4, 1, 0, NULL, NULL, 'pop', '', '2026-06-14 12:47:51', '2026-06-14 12:47:51', 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `olt_photos`
--

CREATE TABLE `olt_photos` (
  `id` int(11) NOT NULL,
  `olt_id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `file_size` int(11) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `olt_ports`
--

CREATE TABLE `olt_ports` (
  `id` int(11) NOT NULL,
  `olt_id` int(11) NOT NULL,
  `port_number` int(11) NOT NULL,
  `status` enum('available','used','maintenance') DEFAULT 'available',
  `target_odc_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pole`
--

CREATE TABLE `pole` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `lat` decimal(10,8) NOT NULL,
  `lng` decimal(11,8) NOT NULL,
  `location` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `jenis_tiang` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pole`
--

INSERT INTO `pole` (`id`, `name`, `lat`, `lng`, `location`, `description`, `created_at`, `updated_at`, `jenis_tiang`) VALUES
(1, 'tiang 001', -6.96096527, 109.63897347, 'test', '', '2026-06-17 07:43:21', '2026-06-17 07:43:21', NULL),
(2, 'tiang 004', -6.96257338, 109.64205265, 'asdf', '', '2026-06-17 07:43:58', '2026-06-17 08:04:06', ''),
(3, 'tiang 002', -6.96088539, 109.63749826, 'test', '', '2026-06-17 07:44:36', '2026-06-17 07:44:36', NULL),
(4, 'tiang 003', -6.96142054, 109.63994443, 'test', '', '2026-06-17 07:44:53', '2026-06-17 07:44:53', NULL),
(5, 'tiang 005', -6.96131405, 109.63686526, '', '', '2026-06-17 07:54:40', '2026-06-17 07:54:40', '6'),
(6, 'tiang 007', -6.96142853, 109.64096904, '', '', '2026-06-17 09:13:52', '2026-06-17 09:13:52', ''),
(7, 'tiang 008', -6.96204089, 109.64467049, '', '', '2026-06-17 09:14:05', '2026-06-17 09:14:05', '');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pon`
--

CREATE TABLE `pon` (
  `id` int(11) NOT NULL,
  `olt_id` int(11) NOT NULL,
  `card_number` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `port_count` int(11) DEFAULT 8,
  `status` enum('active','inactive','maintenance') DEFAULT 'active',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pon`
--

INSERT INTO `pon` (`id`, `olt_id`, `card_number`, `name`, `port_count`, `status`, `description`, `created_at`, `updated_at`) VALUES
(10, 6, 1, 'PON Card 1', 4, 'active', NULL, '2026-06-14 12:47:51', '2026-06-14 12:47:51');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pon_ports`
--

CREATE TABLE `pon_ports` (
  `id` int(11) NOT NULL,
  `pon_id` int(11) NOT NULL,
  `port_number` int(11) NOT NULL,
  `status` enum('available','used','maintenance') DEFAULT 'available',
  `target_odc_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pon_ports`
--

INSERT INTO `pon_ports` (`id`, `pon_id`, `port_number`, `status`, `target_odc_id`, `description`, `created_at`, `updated_at`) VALUES
(57, 10, 1, 'used', 12, NULL, '2026-06-14 12:47:51', '2026-06-14 12:48:50'),
(58, 10, 2, 'used', 13, NULL, '2026-06-14 12:47:51', '2026-06-16 08:02:16'),
(59, 10, 3, 'available', NULL, NULL, '2026-06-14 12:47:51', '2026-06-14 12:47:51'),
(60, 10, 4, 'available', NULL, NULL, '2026-06-14 12:47:51', '2026-06-14 12:47:51');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pop`
--

CREATE TABLE `pop` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `code` varchar(50) DEFAULT NULL,
  `lat` decimal(10,8) NOT NULL,
  `lng` decimal(11,8) NOT NULL,
  `location` varchar(255) NOT NULL,
  `address` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `has_photo` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pop`
--

INSERT INTO `pop` (`id`, `name`, `code`, `lat`, `lng`, `location`, `address`, `description`, `created_at`, `updated_at`, `has_photo`) VALUES
(5, 'pop kedungwuni ', 'kdw-01', -6.96176930, 109.64694430, 'kedungwuni ', 'kedungwuni ', '', '2026-06-14 12:47:11', '2026-06-14 12:47:11', 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `pop_photos`
--

CREATE TABLE `pop_photos` (
  `id` int(11) NOT NULL,
  `pop_id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `file_size` int(11) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `port_photos`
--

CREATE TABLE `port_photos` (
  `id` int(11) NOT NULL,
  `port_id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `file_size` int(11) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `role` enum('admin','operator','viewer') DEFAULT 'operator',
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `phone`, `email`, `notes`, `role`, `is_active`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', NULL, NULL, NULL, 'admin', 1, '2026-06-17 06:38:36', '2026-04-29 05:17:32', '2026-06-17 06:38:36'),
(2, 'operator', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Operator Lapangan', NULL, NULL, NULL, 'operator', 1, '2026-05-10 06:05:02', '2026-04-29 05:17:32', '2026-05-10 06:05:02'),
(3, 'viewer', '$2y$10$MGgSxghgkLq14k7q5jnA6u6nDAQ2YKcfdE/DkL/TOrvOvuTRz7S8m', 'Viewer Only', NULL, NULL, NULL, 'viewer', 1, '2026-06-16 08:05:32', '2026-04-29 05:17:32', '2026-06-16 08:05:32'),
(4, 'fadil', '$2y$10$bYKbB0u6c5WcTi7Vu12cauf0FxEtLpfhA03HFQf1RNf02N1IMplPm', 'fadilmubarok', '085878532124', 'ffadil2208@gmail.com', 'test', 'admin', 1, '2026-06-06 09:40:18', '2026-05-10 06:07:44', '2026-06-06 09:40:18');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `login_logs`
--
ALTER TABLE `login_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `odc`
--
ALTER TABLE `odc`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_source` (`source_type`,`source_id`);

--
-- Indeks untuk tabel `odc_odp_connections`
--
ALTER TABLE `odc_odp_connections`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_connection` (`odc_id`,`odp_id`),
  ADD KEY `odp_id` (`odp_id`);

--
-- Indeks untuk tabel `odc_photos`
--
ALTER TABLE `odc_photos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `odc_id` (`odc_id`);

--
-- Indeks untuk tabel `odp`
--
ALTER TABLE `odp`
  ADD PRIMARY KEY (`id`),
  ADD KEY `source_id` (`source_id`);

--
-- Indeks untuk tabel `odp_photos`
--
ALTER TABLE `odp_photos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `odp_id` (`odp_id`);

--
-- Indeks untuk tabel `odp_ports`
--
ALTER TABLE `odp_ports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_odp_port` (`odp_id`,`port_number`);

--
-- Indeks untuk tabel `olt`
--
ALTER TABLE `olt`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pop_id` (`pop_id`);

--
-- Indeks untuk tabel `olt_photos`
--
ALTER TABLE `olt_photos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `olt_id` (`olt_id`);

--
-- Indeks untuk tabel `olt_ports`
--
ALTER TABLE `olt_ports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_olt_port` (`olt_id`,`port_number`),
  ADD KEY `olt_id` (`olt_id`),
  ADD KEY `target_odc_id` (`target_odc_id`);

--
-- Indeks untuk tabel `pole`
--
ALTER TABLE `pole`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pole_name` (`name`);

--
-- Indeks untuk tabel `pon`
--
ALTER TABLE `pon`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_olt_card` (`olt_id`,`card_number`),
  ADD KEY `olt_id` (`olt_id`);

--
-- Indeks untuk tabel `pon_ports`
--
ALTER TABLE `pon_ports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_pon_port` (`pon_id`,`port_number`),
  ADD KEY `pon_id` (`pon_id`),
  ADD KEY `target_odc_id` (`target_odc_id`);

--
-- Indeks untuk tabel `pop`
--
ALTER TABLE `pop`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `pop_photos`
--
ALTER TABLE `pop_photos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pop_id` (`pop_id`);

--
-- Indeks untuk tabel `port_photos`
--
ALTER TABLE `port_photos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `port_id` (`port_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `login_logs`
--
ALTER TABLE `login_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT untuk tabel `odc`
--
ALTER TABLE `odc`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `odc_odp_connections`
--
ALTER TABLE `odc_odp_connections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT untuk tabel `odc_photos`
--
ALTER TABLE `odc_photos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `odp`
--
ALTER TABLE `odp`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT untuk tabel `odp_photos`
--
ALTER TABLE `odp_photos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `odp_ports`
--
ALTER TABLE `odp_ports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=169;

--
-- AUTO_INCREMENT untuk tabel `olt`
--
ALTER TABLE `olt`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `olt_photos`
--
ALTER TABLE `olt_photos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `olt_ports`
--
ALTER TABLE `olt_ports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT untuk tabel `pole`
--
ALTER TABLE `pole`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `pon`
--
ALTER TABLE `pon`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `pon_ports`
--
ALTER TABLE `pon_ports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT untuk tabel `pop`
--
ALTER TABLE `pop`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `pop_photos`
--
ALTER TABLE `pop_photos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `port_photos`
--
ALTER TABLE `port_photos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `login_logs`
--
ALTER TABLE `login_logs`
  ADD CONSTRAINT `login_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `odc_odp_connections`
--
ALTER TABLE `odc_odp_connections`
  ADD CONSTRAINT `odc_odp_connections_ibfk_1` FOREIGN KEY (`odc_id`) REFERENCES `odc` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `odc_odp_connections_ibfk_2` FOREIGN KEY (`odp_id`) REFERENCES `odp` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `odc_photos`
--
ALTER TABLE `odc_photos`
  ADD CONSTRAINT `odc_photos_ibfk_1` FOREIGN KEY (`odc_id`) REFERENCES `odc` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `odp`
--
ALTER TABLE `odp`
  ADD CONSTRAINT `odp_ibfk_1` FOREIGN KEY (`source_id`) REFERENCES `odc` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `odp_photos`
--
ALTER TABLE `odp_photos`
  ADD CONSTRAINT `odp_photos_ibfk_1` FOREIGN KEY (`odp_id`) REFERENCES `odp` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `odp_ports`
--
ALTER TABLE `odp_ports`
  ADD CONSTRAINT `odp_ports_ibfk_1` FOREIGN KEY (`odp_id`) REFERENCES `odp` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `olt`
--
ALTER TABLE `olt`
  ADD CONSTRAINT `olt_ibfk_1` FOREIGN KEY (`pop_id`) REFERENCES `pop` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `olt_photos`
--
ALTER TABLE `olt_photos`
  ADD CONSTRAINT `olt_photos_ibfk_1` FOREIGN KEY (`olt_id`) REFERENCES `olt` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `olt_ports`
--
ALTER TABLE `olt_ports`
  ADD CONSTRAINT `olt_ports_ibfk_1` FOREIGN KEY (`olt_id`) REFERENCES `olt` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `olt_ports_ibfk_2` FOREIGN KEY (`target_odc_id`) REFERENCES `odc` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `pon`
--
ALTER TABLE `pon`
  ADD CONSTRAINT `pon_ibfk_1` FOREIGN KEY (`olt_id`) REFERENCES `olt` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `pon_ports`
--
ALTER TABLE `pon_ports`
  ADD CONSTRAINT `pon_ports_ibfk_1` FOREIGN KEY (`pon_id`) REFERENCES `pon` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pon_ports_ibfk_2` FOREIGN KEY (`target_odc_id`) REFERENCES `odc` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `pop_photos`
--
ALTER TABLE `pop_photos`
  ADD CONSTRAINT `pop_photos_ibfk_1` FOREIGN KEY (`pop_id`) REFERENCES `pop` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `port_photos`
--
ALTER TABLE `port_photos`
  ADD CONSTRAINT `port_photos_ibfk_1` FOREIGN KEY (`port_id`) REFERENCES `odp_ports` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
