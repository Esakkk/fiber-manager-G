-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 24 Bulan Mei 2026 pada 14.28
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
(3, 4, '2026-05-23 15:05:21', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'success');

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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `odc`
--

INSERT INTO `odc` (`id`, `name`, `lat`, `lng`, `location`, `capacity`, `used_ports`, `description`, `source_type`, `source_id`, `pon_id`, `pon_port_number`, `olt_id`, `created_at`, `updated_at`) VALUES
(1, 'MS 1', -6.96620750, 109.64638359, 'Gembong', 8, 4, 'odc gembong 1', 'pop', 1, NULL, 1, NULL, '2026-05-22 05:34:38', '2026-05-23 07:31:45'),
(3, '13', -6.96750832, 109.64612441, '123', 24, 0, '123', NULL, NULL, NULL, NULL, NULL, '2026-05-23 15:20:12', '2026-05-23 15:20:12'),
(4, 'hy', -6.96620750, 109.64638359, '3', 24, 0, '', NULL, NULL, NULL, NULL, NULL, '2026-05-23 15:29:22', '2026-05-23 15:29:22');

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
(4, 1, 2, 1, '2026-05-22 05:57:56'),
(7, 1, 3, 2, '2026-05-22 06:02:54'),
(9, 1, 5, 4, '2026-05-22 06:05:17'),
(12, 1, 7, 5, '2026-05-22 11:44:55');

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
(1, 1, 'odc_1_1779428081_6a0feaf10a428.jpg', 'photo_2026-05-10_17-43-33.jpg', 215844, 0, '2026-05-22 05:34:41');

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
(2, 'ODP 1', 1, 1, 'odc', -6.96634459, 109.64808979, '[[-6.96634459,109.64808979],[-6.966354342730768,109.64805576950313],[-6.966276800099248,109.64784353971483],[-6.966259494445827,109.6476222574711],[-6.966298099364121,109.64737348258497],[-6.966312742608155,109.64726351201534],[-6.966308748996194,109.64713945984842],[-6.9662075,109.64638359]]', 'gembong', 8, 8, 'trest', '2026-05-22 05:56:34', '2026-05-22 05:57:39'),
(3, 'ODP 2', 1, 2, 'odc', -6.96695389, 109.64733756, '[[-6.96695389,109.64733756],[-6.966737396486303,109.64668080210689],[-6.9666788235604695,109.64637771248819],[-6.9662075,109.64638359]]', 'gembong', 8, 8, '', '2026-05-22 06:01:10', '2026-05-22 06:01:29'),
(4, 'ODP 3', 1, 3, 'odc', -6.96536361, 109.64782544, '[[-6.96536361,109.64782544],[-6.965221154071955,109.64734867215158],[-6.965129300774344,109.64653730392457],[-6.9662075,109.64638359]]', 'test', 8, 8, '', '2026-05-22 06:02:23', '2026-05-22 06:03:34'),
(5, 'ODP 4', 1, 4, 'odc', -6.96573045, 109.64529304, '[[-6.96573045,109.64529304],[-6.96575496780773,109.64549928903583],[-6.965733668518177,109.64574471116067],[-6.9656924011419505,109.6460182964802],[-6.965632496879667,109.64619532227518],[-6.965625175247077,109.64636564254761],[-6.965624760281609,109.64646666857612],[-6.9662075,109.64638359]]', 'gembong', 8, 8, '', '2026-05-22 06:04:08', '2026-05-22 06:05:17'),
(7, 'ODP 5', 1, 5, 'odc', -6.96658435, 109.64505886, '[[-6.96658435,109.64505886],[-6.966674829951625,109.64550733566286],[-6.966782657378539,109.64626237750056],[-6.966787982189105,109.64637100696565],[-6.9666788235604695,109.64637771248819],[-6.9662075,109.64638359]]', 'gembong', 8, 2, '', '2026-05-22 06:06:06', '2026-05-22 09:09:42');

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

--
-- Dumping data untuk tabel `odp_photos`
--

INSERT INTO `odp_photos` (`id`, `odp_id`, `filename`, `original_name`, `file_size`, `is_primary`, `created_at`) VALUES
(2, 7, 'odp_7_1779450295_9b7c73633784af36.jpg', 'photo_2026-05-10_17-43-33.jpg', 215844, 0, '2026-05-22 11:44:55');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `odp_ports`
--

INSERT INTO `odp_ports` (`id`, `odp_id`, `port_number`, `status`, `target`, `connection_type`, `target_port`, `created_at`, `updated_at`) VALUES
(17, 2, 1, 'available', NULL, NULL, NULL, '2026-05-22 05:56:34', '2026-05-22 05:56:34'),
(18, 2, 2, 'available', NULL, NULL, NULL, '2026-05-22 05:56:34', '2026-05-22 05:56:34'),
(19, 2, 3, 'available', NULL, NULL, NULL, '2026-05-22 05:56:34', '2026-05-22 05:56:34'),
(20, 2, 4, 'available', NULL, NULL, NULL, '2026-05-22 05:56:34', '2026-05-22 05:56:34'),
(21, 2, 5, 'available', NULL, NULL, NULL, '2026-05-22 05:56:34', '2026-05-22 05:56:34'),
(22, 2, 6, 'available', NULL, NULL, NULL, '2026-05-22 05:56:34', '2026-05-22 05:56:34'),
(23, 2, 7, 'available', NULL, NULL, NULL, '2026-05-22 05:56:34', '2026-05-22 05:56:34'),
(24, 2, 8, 'available', NULL, NULL, NULL, '2026-05-22 05:56:34', '2026-05-22 05:56:34'),
(25, 3, 1, 'available', NULL, NULL, NULL, '2026-05-22 06:01:10', '2026-05-22 06:01:10'),
(26, 3, 2, 'available', NULL, NULL, NULL, '2026-05-22 06:01:10', '2026-05-22 06:01:10'),
(27, 3, 3, 'available', NULL, NULL, NULL, '2026-05-22 06:01:10', '2026-05-22 06:01:10'),
(28, 3, 4, 'available', NULL, NULL, NULL, '2026-05-22 06:01:10', '2026-05-22 06:01:10'),
(29, 3, 5, 'available', NULL, NULL, NULL, '2026-05-22 06:01:10', '2026-05-22 06:01:10'),
(30, 3, 6, 'available', NULL, NULL, NULL, '2026-05-22 06:01:10', '2026-05-22 06:01:10'),
(31, 3, 7, 'available', NULL, NULL, NULL, '2026-05-22 06:01:10', '2026-05-22 06:01:10'),
(32, 3, 8, 'available', NULL, NULL, NULL, '2026-05-22 06:01:10', '2026-05-22 06:01:10'),
(33, 4, 1, 'available', NULL, NULL, NULL, '2026-05-22 06:02:23', '2026-05-22 06:02:23'),
(34, 4, 2, 'available', NULL, NULL, NULL, '2026-05-22 06:02:23', '2026-05-22 06:02:23'),
(35, 4, 3, 'available', NULL, NULL, NULL, '2026-05-22 06:02:23', '2026-05-22 06:02:23'),
(36, 4, 4, 'available', NULL, NULL, NULL, '2026-05-22 06:02:23', '2026-05-22 06:02:23'),
(37, 4, 5, 'available', NULL, NULL, NULL, '2026-05-22 06:02:23', '2026-05-22 06:02:23'),
(38, 4, 6, 'available', NULL, NULL, NULL, '2026-05-22 06:02:23', '2026-05-22 06:02:23'),
(39, 4, 7, 'available', NULL, NULL, NULL, '2026-05-22 06:02:23', '2026-05-22 06:02:23'),
(40, 4, 8, 'available', NULL, NULL, NULL, '2026-05-22 06:02:23', '2026-05-22 06:02:23'),
(41, 5, 1, 'available', NULL, NULL, NULL, '2026-05-22 06:04:08', '2026-05-22 06:04:08'),
(42, 5, 2, 'available', NULL, NULL, NULL, '2026-05-22 06:04:08', '2026-05-22 06:04:08'),
(43, 5, 3, 'available', NULL, NULL, NULL, '2026-05-22 06:04:08', '2026-05-22 06:04:08'),
(44, 5, 4, 'available', NULL, NULL, NULL, '2026-05-22 06:04:08', '2026-05-22 06:04:08'),
(45, 5, 5, 'available', NULL, NULL, NULL, '2026-05-22 06:04:08', '2026-05-22 06:04:08'),
(46, 5, 6, 'available', NULL, NULL, NULL, '2026-05-22 06:04:08', '2026-05-22 06:04:08'),
(47, 5, 7, 'available', NULL, NULL, NULL, '2026-05-22 06:04:08', '2026-05-22 06:04:08'),
(48, 5, 8, 'available', NULL, NULL, NULL, '2026-05-22 06:04:08', '2026-05-22 06:04:08'),
(57, 7, 1, 'used', 'AanAdyanKuripan0102026@qc.net', 'drop', NULL, '2026-05-22 06:06:06', '2026-05-22 09:08:36'),
(58, 7, 2, 'used', 'testpelanggan', 'drop', NULL, '2026-05-22 06:06:06', '2026-05-22 09:08:50'),
(59, 7, 3, 'used', 'anti', 'drop', NULL, '2026-05-22 06:06:06', '2026-05-22 09:09:24'),
(60, 7, 4, 'used', 'andi', 'drop', NULL, '2026-05-22 06:06:06', '2026-05-22 09:09:33'),
(61, 7, 5, 'used', 'adsfasdf', 'drop', NULL, '2026-05-22 06:06:06', '2026-05-22 09:09:38'),
(62, 7, 6, 'used', 'qwerqwerq', 'drop', NULL, '2026-05-22 06:06:06', '2026-05-22 09:09:42'),
(63, 7, 7, 'available', NULL, NULL, NULL, '2026-05-22 06:06:06', '2026-05-22 06:06:06'),
(64, 7, 8, 'available', NULL, NULL, NULL, '2026-05-22 06:06:06', '2026-05-22 06:06:06');

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
(2, 1, 'olt 1', 'zte c300', '192', 22, 16, 8, 0, NULL, NULL, 'gembong', '', '2026-05-23 07:34:25', '2026-05-23 13:52:13', 0),
(3, 3, 'olt 1', 'olt 1', '192', 22, 16, 1, 0, NULL, NULL, '', '', '2026-05-23 14:09:58', '2026-05-23 14:44:16', 0),
(4, 2, '123', '123', '123', 22, 8, 2, 0, NULL, NULL, '123', '', '2026-05-23 14:32:18', '2026-05-23 14:32:36', 0),
(5, 1, 'olt 2', 'asdf', '1234', 22, 16, 4, 0, NULL, NULL, 'sadf', 'asdf', '2026-05-24 01:30:28', '2026-05-24 01:30:28', 0);

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

--
-- Dumping data untuk tabel `olt_ports`
--

INSERT INTO `olt_ports` (`id`, `olt_id`, `port_number`, `status`, `target_odc_id`, `description`, `created_at`, `updated_at`) VALUES
(1, 3, 1, 'available', NULL, NULL, '2026-05-23 14:09:58', '2026-05-23 14:09:58'),
(2, 3, 2, 'available', NULL, NULL, '2026-05-23 14:09:58', '2026-05-23 14:09:58'),
(3, 3, 3, 'available', NULL, NULL, '2026-05-23 14:09:58', '2026-05-23 14:09:58'),
(4, 3, 4, 'available', NULL, NULL, '2026-05-23 14:09:58', '2026-05-23 14:09:58'),
(5, 3, 5, 'available', NULL, NULL, '2026-05-23 14:09:58', '2026-05-23 14:09:58'),
(6, 3, 6, 'available', NULL, NULL, '2026-05-23 14:09:58', '2026-05-23 14:09:58'),
(7, 3, 7, 'available', NULL, NULL, '2026-05-23 14:09:58', '2026-05-23 14:09:58'),
(8, 3, 8, 'available', NULL, NULL, '2026-05-23 14:09:58', '2026-05-23 14:09:58'),
(9, 3, 9, 'available', NULL, NULL, '2026-05-23 14:09:58', '2026-05-23 14:09:58'),
(10, 3, 10, 'available', NULL, NULL, '2026-05-23 14:09:58', '2026-05-23 14:09:58'),
(11, 3, 11, 'available', NULL, NULL, '2026-05-23 14:09:58', '2026-05-23 14:09:58'),
(12, 3, 12, 'available', NULL, NULL, '2026-05-23 14:09:58', '2026-05-23 14:09:58'),
(13, 3, 13, 'available', NULL, NULL, '2026-05-23 14:09:58', '2026-05-23 14:09:58'),
(14, 3, 14, 'available', NULL, NULL, '2026-05-23 14:09:58', '2026-05-23 14:09:58'),
(15, 3, 15, 'available', NULL, NULL, '2026-05-23 14:09:58', '2026-05-23 14:09:58'),
(16, 3, 16, 'available', NULL, NULL, '2026-05-23 14:09:58', '2026-05-23 14:09:58');

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
(1, 4, 1, 'PON Card 1', 8, 'active', NULL, '2026-05-23 14:32:18', '2026-05-23 14:32:18'),
(2, 4, 2, '123', 8, 'active', '', '2026-05-23 14:32:36', '2026-05-23 14:32:36'),
(3, 3, 2, 'card 1', 8, 'active', '', '2026-05-23 14:44:16', '2026-05-23 14:44:16'),
(4, 2, 1, 'test', 8, 'active', '', '2026-05-23 14:56:06', '2026-05-23 14:56:06'),
(5, 5, 1, 'PON Card 1', 4, 'active', NULL, '2026-05-24 01:30:28', '2026-05-24 01:30:28'),
(6, 5, 2, 'PON Card 2', 4, 'active', NULL, '2026-05-24 01:30:28', '2026-05-24 01:30:28'),
(7, 5, 3, 'PON Card 3', 4, 'active', NULL, '2026-05-24 01:30:28', '2026-05-24 01:30:28'),
(8, 5, 4, 'PON Card 4', 4, 'active', NULL, '2026-05-24 01:30:28', '2026-05-24 01:30:28');

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
(1, 1, 1, 'available', NULL, NULL, '2026-05-23 14:32:18', '2026-05-23 14:32:18'),
(2, 1, 2, 'available', NULL, NULL, '2026-05-23 14:32:18', '2026-05-23 14:32:18'),
(3, 1, 3, 'available', NULL, NULL, '2026-05-23 14:32:18', '2026-05-23 14:32:18'),
(4, 1, 4, 'available', NULL, NULL, '2026-05-23 14:32:18', '2026-05-23 14:32:18'),
(5, 1, 5, 'available', NULL, NULL, '2026-05-23 14:32:18', '2026-05-23 14:32:18'),
(6, 1, 6, 'available', NULL, NULL, '2026-05-23 14:32:18', '2026-05-23 14:32:18'),
(7, 1, 7, 'available', NULL, NULL, '2026-05-23 14:32:18', '2026-05-23 14:32:18'),
(8, 1, 8, 'available', NULL, NULL, '2026-05-23 14:32:18', '2026-05-23 14:32:18'),
(9, 2, 1, 'available', NULL, NULL, '2026-05-23 14:32:36', '2026-05-23 14:32:36'),
(10, 2, 2, 'available', NULL, NULL, '2026-05-23 14:32:36', '2026-05-23 14:32:36'),
(11, 2, 3, 'available', NULL, NULL, '2026-05-23 14:32:36', '2026-05-23 14:32:36'),
(12, 2, 4, 'available', NULL, NULL, '2026-05-23 14:32:36', '2026-05-23 14:32:36'),
(13, 2, 5, 'available', NULL, NULL, '2026-05-23 14:32:36', '2026-05-23 14:32:36'),
(14, 2, 6, 'available', NULL, NULL, '2026-05-23 14:32:36', '2026-05-23 14:32:36'),
(15, 2, 7, 'available', NULL, NULL, '2026-05-23 14:32:36', '2026-05-23 14:32:36'),
(16, 2, 8, 'available', NULL, NULL, '2026-05-23 14:32:36', '2026-05-23 14:32:36'),
(17, 3, 1, 'used', 1, NULL, '2026-05-23 14:44:16', '2026-05-24 12:22:57'),
(18, 3, 2, 'used', 3, NULL, '2026-05-23 14:44:16', '2026-05-24 12:23:19'),
(19, 3, 3, 'available', NULL, NULL, '2026-05-23 14:44:16', '2026-05-23 14:44:16'),
(20, 3, 4, 'available', NULL, NULL, '2026-05-23 14:44:16', '2026-05-23 14:44:16'),
(21, 3, 5, 'available', NULL, NULL, '2026-05-23 14:44:16', '2026-05-23 14:44:16'),
(22, 3, 6, 'available', NULL, NULL, '2026-05-23 14:44:16', '2026-05-23 14:44:16'),
(23, 3, 7, 'available', NULL, NULL, '2026-05-23 14:44:16', '2026-05-23 14:44:16'),
(24, 3, 8, 'available', NULL, NULL, '2026-05-23 14:44:16', '2026-05-23 14:44:16'),
(25, 4, 1, 'available', NULL, NULL, '2026-05-23 14:56:06', '2026-05-23 14:56:06'),
(26, 4, 2, 'available', NULL, NULL, '2026-05-23 14:56:06', '2026-05-23 14:56:06'),
(27, 4, 3, 'available', NULL, NULL, '2026-05-23 14:56:06', '2026-05-23 14:56:06'),
(28, 4, 4, 'available', NULL, NULL, '2026-05-23 14:56:06', '2026-05-23 14:56:06'),
(29, 4, 5, 'available', NULL, NULL, '2026-05-23 14:56:06', '2026-05-23 14:56:06'),
(30, 4, 6, 'available', NULL, NULL, '2026-05-23 14:56:06', '2026-05-23 14:56:06'),
(31, 4, 7, 'available', NULL, NULL, '2026-05-23 14:56:06', '2026-05-23 14:56:06'),
(32, 4, 8, 'available', NULL, NULL, '2026-05-23 14:56:06', '2026-05-23 14:56:06'),
(33, 5, 1, 'available', NULL, NULL, '2026-05-24 01:30:28', '2026-05-24 01:30:28'),
(34, 5, 2, 'available', NULL, NULL, '2026-05-24 01:30:28', '2026-05-24 01:30:28'),
(35, 5, 3, 'available', NULL, NULL, '2026-05-24 01:30:28', '2026-05-24 01:30:28'),
(36, 5, 4, 'available', NULL, NULL, '2026-05-24 01:30:28', '2026-05-24 01:30:28'),
(37, 6, 1, 'available', NULL, NULL, '2026-05-24 01:30:28', '2026-05-24 01:30:28'),
(38, 6, 2, 'available', NULL, NULL, '2026-05-24 01:30:28', '2026-05-24 01:30:28'),
(39, 6, 3, 'available', NULL, NULL, '2026-05-24 01:30:28', '2026-05-24 01:30:28'),
(40, 6, 4, 'available', NULL, NULL, '2026-05-24 01:30:28', '2026-05-24 01:30:28'),
(41, 7, 1, 'available', NULL, NULL, '2026-05-24 01:30:28', '2026-05-24 01:30:28'),
(42, 7, 2, 'available', NULL, NULL, '2026-05-24 01:30:28', '2026-05-24 01:30:28'),
(43, 7, 3, 'available', NULL, NULL, '2026-05-24 01:30:28', '2026-05-24 01:30:28'),
(44, 7, 4, 'available', NULL, NULL, '2026-05-24 01:30:28', '2026-05-24 01:30:28'),
(45, 8, 1, 'available', NULL, NULL, '2026-05-24 01:30:28', '2026-05-24 01:30:28'),
(46, 8, 2, 'available', NULL, NULL, '2026-05-24 01:30:28', '2026-05-24 01:30:28'),
(47, 8, 3, 'available', NULL, NULL, '2026-05-24 01:30:28', '2026-05-24 01:30:28'),
(48, 8, 4, 'available', NULL, NULL, '2026-05-24 01:30:28', '2026-05-24 01:30:28');

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
(1, 'gembong', '1', -6.96554762, 109.64794461, 'gembong', 'gembong', 'gembong', '2026-05-23 04:30:47', '2026-05-23 07:33:48', 0),
(2, 'test', 'test', 99.99999999, 132.00000000, 'test', 'test', 'test', '2026-05-23 13:50:27', '2026-05-23 13:50:27', 0),
(3, 'test2', 'test2', 99.99999999, 999.99999999, 'test 2', 'test 2', 'test 2', '2026-05-23 14:09:41', '2026-05-23 14:09:41', 0);

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
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', NULL, NULL, NULL, 'admin', 1, '2026-05-20 10:51:31', '2026-04-29 05:17:32', '2026-05-20 10:51:31'),
(2, 'operator', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Operator Lapangan', NULL, NULL, NULL, 'operator', 1, '2026-05-10 06:05:02', '2026-04-29 05:17:32', '2026-05-10 06:05:02'),
(3, 'viewer', '$2y$10$MGgSxghgkLq14k7q5jnA6u6nDAQ2YKcfdE/DkL/TOrvOvuTRz7S8m', 'Viewer Only', NULL, NULL, NULL, 'viewer', 1, '2026-05-10 06:14:09', '2026-04-29 05:17:32', '2026-05-15 01:01:07'),
(4, 'fadil', '$2y$10$bYKbB0u6c5WcTi7Vu12cauf0FxEtLpfhA03HFQf1RNf02N1IMplPm', 'fadilmubarok', '085878532124', 'ffadil2208@gmail.com', 'test', 'admin', 1, '2026-05-23 15:05:21', '2026-05-10 06:07:44', '2026-05-23 15:05:21');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `odc`
--
ALTER TABLE `odc`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `odc_odp_connections`
--
ALTER TABLE `odc_odp_connections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `odc_photos`
--
ALTER TABLE `odc_photos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `odp`
--
ALTER TABLE `odp`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `odp_photos`
--
ALTER TABLE `odp_photos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `odp_ports`
--
ALTER TABLE `odp_ports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT untuk tabel `olt`
--
ALTER TABLE `olt`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
-- AUTO_INCREMENT untuk tabel `pon`
--
ALTER TABLE `pon`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `pon_ports`
--
ALTER TABLE `pon_ports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT untuk tabel `pop`
--
ALTER TABLE `pop`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `pop_photos`
--
ALTER TABLE `pop_photos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
