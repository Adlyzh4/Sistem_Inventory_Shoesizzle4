-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 30 Jun 2025 pada 11.42
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `shoesizzle_db`
--

DELIMITER $$
--
-- Prosedur
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `generate_transaction_number` (IN `p_type` VARCHAR(10), OUT `p_number` VARCHAR(30))   BEGIN
    DECLARE v_count INT;
    DECLARE v_date VARCHAR(8);
    
    SET v_date = DATE_FORMAT(NOW(), '%Y%m%d');
    
    IF p_type = 'MASUK' THEN
        SELECT COUNT(*) + 1 INTO v_count FROM barang_masuk WHERE DATE(tanggal_masuk) = CURDATE();
        SET p_number = CONCAT('MSK-', v_date, '-', LPAD(v_count, 4, '0'));
    ELSEIF p_type = 'KELUAR' THEN
        SELECT COUNT(*) + 1 INTO v_count FROM barang_keluar WHERE DATE(tanggal_keluar) = CURDATE();
        SET p_number = CONCAT('KLR-', v_date, '-', LPAD(v_count, 4, '0'));
    ELSEIF p_type = 'RETUR' THEN
        SELECT COUNT(*) + 1 INTO v_count FROM retur_barang WHERE DATE(tanggal_retur) = CURDATE();
        SET p_number = CONCAT('RTR-', v_date, '-', LPAD(v_count, 4, '0'));
    END IF;
END$$

--
-- Fungsi
--
CREATE DEFINER=`root`@`localhost` FUNCTION `calculate_return_percentage` (`p_barang_id` INT, `p_bulan` INT, `p_tahun` INT) RETURNS DECIMAL(5,2) DETERMINISTIC READS SQL DATA BEGIN
    DECLARE v_total_keluar INT DEFAULT 0;
    DECLARE v_total_retur INT DEFAULT 0;
    DECLARE v_percentage DECIMAL(5,2) DEFAULT 0;
    
    -- Hitung total barang keluar
    SELECT COALESCE(SUM(dbk.jumlah), 0) INTO v_total_keluar
    FROM barang_keluar bk
    JOIN detail_barang_keluar dbk ON bk.keluar_id = dbk.keluar_id
    WHERE dbk.barang_id = p_barang_id 
    AND MONTH(bk.tanggal_keluar) = p_bulan 
    AND YEAR(bk.tanggal_keluar) = p_tahun;
    
    -- Hitung total retur
    SELECT COALESCE(SUM(drb.jumlah_retur), 0) INTO v_total_retur
    FROM retur_barang rb
    JOIN detail_retur_barang drb ON rb.retur_id = drb.retur_id
    WHERE drb.barang_id = p_barang_id 
    AND MONTH(rb.tanggal_retur) = p_bulan 
    AND YEAR(rb.tanggal_retur) = p_tahun;
    
    -- Hitung persentase
    IF v_total_keluar > 0 THEN
        SET v_percentage = (v_total_retur / v_total_keluar) * 100;
    END IF;
    
    RETURN v_percentage;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `barang`
--

CREATE TABLE `barang` (
  `barang_id` int(11) NOT NULL,
  `kode_barang` varchar(20) NOT NULL,
  `nama_barang` varchar(100) NOT NULL,
  `kategori_id` int(11) DEFAULT NULL,
  `merek_id` int(11) DEFAULT NULL,
  `ukuran` varchar(10) DEFAULT NULL,
  `warna` varchar(30) DEFAULT NULL,
  `harga_beli` decimal(12,2) NOT NULL,
  `harga_jual` decimal(12,2) NOT NULL,
  `stok_minimum` int(11) DEFAULT 5,
  `stok_aktual` int(11) DEFAULT 0,
  `gambar` varchar(255) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `barang`
--

INSERT INTO `barang` (`barang_id`, `kode_barang`, `nama_barang`, `kategori_id`, `merek_id`, `ukuran`, `warna`, `harga_beli`, `harga_jual`, `stok_minimum`, `stok_aktual`, `gambar`, `deskripsi`, `created_at`, `updated_at`, `is_active`) VALUES
(1, 'SNK001', 'Nike Air Max 90', 1, 1, '42', 'Putih', 800000.00, 1200000.00, 10, 20, NULL, NULL, '2025-06-11 10:42:13', '2025-06-13 13:47:29', 1),
(2, 'SNK002', 'Adidas Ultraboost', 1, 2, '41', 'Hitam', 750000.00, 1150000.00, 8, 12, NULL, NULL, '2025-06-11 10:42:13', '2025-06-23 17:10:28', 1),
(3, 'FRM001', 'Sepatu Formal Hitam', 2, 5, '43', 'Hitam', 800000.00, 1000000.00, 5, 10, NULL, NULL, '2025-06-11 10:42:13', '2025-06-15 08:52:14', 1),
(4, 'BTS001', 'Boots Kulit Coklat', 3, 5, '42', 'Coklat', 400000.00, 650000.00, 8, 15, NULL, '', '2025-06-11 10:42:13', '2025-06-23 17:16:30', 1),
(5, 'SNK003', 'Converse All Star', 1, 3, '40', 'Merah', 450000.00, 650000.00, 10, 15, NULL, NULL, '2025-06-11 10:42:13', '2025-06-13 13:41:49', 1),
(6, 'SNK004', 'Swaylow', 4, 4, '35-40', 'Hijau', 8000.00, 10000.00, 5, 5, NULL, NULL, '2025-06-15 16:05:16', '2025-06-23 16:30:32', 1);

--
-- Trigger `barang`
--
DELIMITER $$
CREATE TRIGGER `check_stok_minimum` AFTER UPDATE ON `barang` FOR EACH ROW BEGIN
    IF NEW.stok_aktual <= NEW.stok_minimum THEN
        INSERT INTO notifikasi (user_id, judul, pesan, jenis)
        SELECT user_id, 'Stok Minimum', 
               CONCAT('Stok barang ', NEW.nama_barang, ' sudah mencapai batas minimum (', NEW.stok_aktual, ')'), 
               'warning'
        FROM users WHERE role IN ('admin', 'owner');
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `barang_keluar`
--

CREATE TABLE `barang_keluar` (
  `keluar_id` int(11) NOT NULL,
  `nomor_transaksi` varchar(30) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `barang_id` int(11) DEFAULT NULL,
  `kode_barang` varchar(50) DEFAULT NULL,
  `nama_barang` varchar(255) DEFAULT NULL,
  `tanggal_keluar` datetime NOT NULL,
  `jenis_keluar` enum('penjualan','retur','rusak','lainnya') DEFAULT 'penjualan',
  `jumlah` int(100) NOT NULL,
  `total_nilai` decimal(15,2) DEFAULT 0.00,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `barang_keluar`
--

INSERT INTO `barang_keluar` (`keluar_id`, `nomor_transaksi`, `user_id`, `barang_id`, `kode_barang`, `nama_barang`, `tanggal_keluar`, `jenis_keluar`, `jumlah`, `total_nilai`, `keterangan`, `created_at`) VALUES
(1, '8123123', NULL, 1, 'SNK001', 'adidas', '2025-06-11 21:47:26', 'penjualan', 2, 2400000.00, 'test aja barang sepatu adidas', '2025-06-11 19:50:49'),
(2, '', NULL, 5, NULL, NULL, '2025-06-13 00:00:00', 'penjualan', 5, 0.00, NULL, '2025-06-13 13:41:49'),
(5, 'aezakmi089', NULL, 2, NULL, NULL, '2025-06-23 00:00:00', 'penjualan', 8, 0.00, NULL, '2025-06-23 17:10:28');

-- --------------------------------------------------------

--
-- Struktur dari tabel `barang_masuk`
--

CREATE TABLE `barang_masuk` (
  `masuk_id` int(11) NOT NULL,
  `nomor_transaksi` varchar(30) NOT NULL,
  `distributor_id` int(11) DEFAULT NULL,
  `barang_id` int(11) DEFAULT NULL,
  `kode_barang` varchar(50) DEFAULT NULL,
  `nama_barang` varchar(255) DEFAULT NULL,
  `jumlah` int(100) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `tanggal_masuk` datetime NOT NULL,
  `total_nilai` decimal(15,2) DEFAULT 0.00,
  `keterangan` text DEFAULT NULL,
  `status` enum('pending','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `barang_masuk`
--

INSERT INTO `barang_masuk` (`masuk_id`, `nomor_transaksi`, `distributor_id`, `barang_id`, `kode_barang`, `nama_barang`, `jumlah`, `user_id`, `tanggal_masuk`, `total_nilai`, `keterangan`, `status`, `created_at`) VALUES
(1, '12324324', 2, 4, 'BTS001', 'sepatu boots', 10, 1, '2025-06-13 13:24:38', 6500000.00, 'stok sepatu boots', 'pending', '2025-06-13 11:27:38'),
(2, '', NULL, 2, NULL, NULL, 5, NULL, '2025-06-13 00:00:00', 0.00, NULL, 'pending', '2025-06-13 13:43:54'),
(3, 'kjkszpj123', NULL, 4, NULL, NULL, 7, NULL, '2025-06-23 00:00:00', 0.00, NULL, 'pending', '2025-06-23 17:16:30');

-- --------------------------------------------------------

--
-- Struktur dari tabel `detail_barang_keluar`
--

CREATE TABLE `detail_barang_keluar` (
  `detail_keluar_id` int(11) NOT NULL,
  `keluar_id` int(11) DEFAULT NULL,
  `barang_id` int(11) DEFAULT NULL,
  `jumlah` int(11) NOT NULL,
  `harga_jual` decimal(12,2) NOT NULL,
  `subtotal` decimal(15,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `detail_barang_keluar`
--

INSERT INTO `detail_barang_keluar` (`detail_keluar_id`, `keluar_id`, `barang_id`, `jumlah`, `harga_jual`, `subtotal`) VALUES
(1, 1, 1, 2, 1200000.00, 2400000.00);

--
-- Trigger `detail_barang_keluar`
--
DELIMITER $$
CREATE TRIGGER `update_stok_keluar` AFTER INSERT ON `detail_barang_keluar` FOR EACH ROW BEGIN
    UPDATE barang 
    SET stok_aktual = stok_aktual - NEW.jumlah,
        updated_at = CURRENT_TIMESTAMP
    WHERE barang_id = NEW.barang_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `detail_barang_masuk`
--

CREATE TABLE `detail_barang_masuk` (
  `detail_masuk_id` int(11) NOT NULL,
  `masuk_id` int(11) DEFAULT NULL,
  `barang_id` int(11) DEFAULT NULL,
  `jumlah` int(11) NOT NULL,
  `harga_beli` decimal(12,2) NOT NULL,
  `subtotal` decimal(15,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Trigger `detail_barang_masuk`
--
DELIMITER $$
CREATE TRIGGER `update_stok_masuk` AFTER INSERT ON `detail_barang_masuk` FOR EACH ROW BEGIN
    UPDATE barang 
    SET stok_aktual = stok_aktual + NEW.jumlah,
        updated_at = CURRENT_TIMESTAMP
    WHERE barang_id = NEW.barang_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `detail_retur_barang`
--

CREATE TABLE `detail_retur_barang` (
  `detail_retur_id` int(11) NOT NULL,
  `retur_id` int(11) DEFAULT NULL,
  `barang_id` int(11) DEFAULT NULL,
  `jumlah_retur` int(11) NOT NULL,
  `harga_beli` decimal(12,2) NOT NULL,
  `kondisi_barang` enum('baik','rusak_ringan','rusak_berat') DEFAULT 'baik',
  `keterangan` text DEFAULT NULL,
  `subtotal` decimal(15,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Trigger `detail_retur_barang`
--
DELIMITER $$
CREATE TRIGGER `update_stok_retur` AFTER INSERT ON `detail_retur_barang` FOR EACH ROW BEGIN
    UPDATE barang 
    SET stok_aktual = stok_aktual - NEW.jumlah_retur,
        updated_at = CURRENT_TIMESTAMP
    WHERE barang_id = NEW.barang_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `distributor`
--

CREATE TABLE `distributor` (
  `distributor_id` int(11) NOT NULL,
  `nama_distributor` varchar(100) NOT NULL,
  `alamat` text NOT NULL,
  `telepon` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `distributor`
--

INSERT INTO `distributor` (`distributor_id`, `nama_distributor`, `alamat`, `telepon`, `email`, `contact_person`, `created_at`, `is_active`) VALUES
(1, 'PT Sepatu Nusantara', 'Jl. Industri No. 123, Jakarta', '021-1234567', 'info@sepatunusantara.com', 'Budi Santoso', '2025-06-11 10:42:13', 1),
(2, 'CV Footwear Indo', 'Jl. Perdagangan No. 456, Bandung', '022-7654321', 'sales@footwearindo.com', 'Siti Nurhaliza', '2025-06-11 10:42:13', 1),
(3, 'Toko Sepatu Makmur', 'Jl. Pasar Baru No. 789, Surabaya', '031-9876543', 'makmur@email.com', 'Ahmad Hidayat', '2025-06-11 10:42:13', 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori`
--

CREATE TABLE `kategori` (
  `kategori_id` int(11) NOT NULL,
  `nama_kategori` varchar(50) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kategori`
--

INSERT INTO `kategori` (`kategori_id`, `nama_kategori`, `deskripsi`, `created_at`) VALUES
(1, 'Sneakers', 'Sepatu olahraga dan kasual', '2025-06-11 10:42:13'),
(2, 'Formal', 'Sepatu formal untuk acara resmi', '2025-06-11 10:42:13'),
(3, 'Boots', 'Sepatu boots untuk gaya kasual', '2025-06-11 10:42:13'),
(4, 'Sandal', 'Sandal dan flip-flop', '2025-06-11 10:42:13'),
(5, 'High Heels', 'Sepatu hak tinggi untuk wanita', '2025-06-11 10:42:13');

-- --------------------------------------------------------

--
-- Struktur dari tabel `laporan_stok`
--

CREATE TABLE `laporan_stok` (
  `laporan_id` int(11) NOT NULL,
  `barang_id` int(11) DEFAULT NULL,
  `stok_awal` int(11) DEFAULT 0,
  `stok_masuk` int(11) DEFAULT 0,
  `stok_keluar` int(11) DEFAULT 0,
  `stok_retur` int(11) DEFAULT 0,
  `stok_akhir` int(11) DEFAULT 0,
  `periode_awal` date NOT NULL,
  `periode_akhir` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `log_aktivitas`
--

CREATE TABLE `log_aktivitas` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `role` varchar(50) DEFAULT NULL,
  `aktivitas` text DEFAULT NULL,
  `waktu` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `log_aktivitas`
--

INSERT INTO `log_aktivitas` (`log_id`, `user_id`, `role`, `aktivitas`, `waktu`) VALUES
(1, 4, NULL, 'Login ke sistem', '2025-06-15 14:36:15'),
(2, 4, NULL, 'Login ke sistem', '2025-06-15 14:43:15'),
(3, 4, NULL, 'Login ke sistem', '2025-06-15 14:47:41'),
(4, 4, NULL, 'Login ke sistem', '2025-06-15 15:02:23'),
(5, 4, NULL, 'Logout dari sistem', '2025-06-15 15:07:25'),
(6, 4, NULL, 'Login ke sistem', '2025-06-15 15:07:32'),
(7, 4, NULL, 'Logout dari sistem', '2025-06-15 15:09:10'),
(8, 4, NULL, 'Login ke sistem', '2025-06-15 15:09:16'),
(9, 4, NULL, 'Menambahkan barang: Sepatu Formal Hitam', '2025-06-15 15:42:08'),
(10, 4, NULL, 'Menambahkan barang: Sepatu Formal Hitam', '2025-06-15 15:44:37'),
(11, 4, NULL, 'Mengedit barang: Sepatu Formal Hitam', '2025-06-15 15:52:14'),
(12, 4, 'admin-db', 'Mengedit barang: Swaylow', '2025-06-15 23:32:33'),
(13, 4, 'admin-db', 'Menambahkan barang: awdawd', '2025-06-15 23:38:36'),
(14, 4, 'admin-db', 'Menghapus barang: ', '2025-06-15 23:39:55'),
(15, 4, 'admin-db', 'Menambahkan barang: qwdadaw', '2025-06-15 23:42:55'),
(16, 4, 'admin-db', 'Menghapus barang: ', '2025-06-15 23:43:07'),
(17, 4, 'admin-db', 'Menambahkan barang: awdadssa', '2025-06-15 23:45:51'),
(18, 4, 'admin-db', 'Menghapus barang: awdadssa', '2025-06-15 23:46:01'),
(19, 4, 'admin-db', 'Logout dari sistem', '2025-06-16 00:36:26'),
(20, 4, 'admin-db', 'Login ke sistem', '2025-06-16 00:36:33'),
(21, NULL, 'unknown', 'Mengedit barang: Swaylow', '2025-06-16 00:43:27'),
(22, 4, 'admin-db', 'Logout dari sistem', '2025-06-16 01:36:57'),
(23, 4, 'admin-db', 'Login ke sistem', '2025-06-17 10:02:01'),
(24, 4, 'admin-db', 'Login ke sistem', '2025-06-23 23:08:50'),
(25, 4, 'admin-db', 'Mengedit barang: Swaylow', '2025-06-23 23:30:32'),
(26, 4, 'admin-db', 'Logout dari sistem', '2025-06-24 00:54:37'),
(27, 4, 'admin-db', 'Login ke sistem', '2025-06-29 23:47:12');

-- --------------------------------------------------------

--
-- Struktur dari tabel `merek`
--

CREATE TABLE `merek` (
  `merek_id` int(11) NOT NULL,
  `nama_merek` varchar(50) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `merek`
--

INSERT INTO `merek` (`merek_id`, `nama_merek`, `deskripsi`, `created_at`) VALUES
(1, 'Nike', 'Merek sepatu olahraga terkenal', '2025-06-11 10:42:13'),
(2, 'Adidas', 'Merek sepatu olahraga internasional', '2025-06-11 10:42:13'),
(3, 'Converse', 'Merek sepatu kasual klasik', '2025-06-11 10:42:13'),
(4, 'Vans', 'Merek sepatu skate dan kasual', '2025-06-11 10:42:13'),
(5, 'Bata', 'Merek sepatu lokal berkualitas', '2025-06-11 10:42:13');

-- --------------------------------------------------------

--
-- Struktur dari tabel `notifikasi`
--

CREATE TABLE `notifikasi` (
  `notifikasi_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `judul` varchar(100) NOT NULL,
  `pesan` text NOT NULL,
  `jenis` enum('info','warning','error','success') DEFAULT 'info',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `notifikasi`
--

INSERT INTO `notifikasi` (`notifikasi_id`, `user_id`, `judul`, `pesan`, `jenis`, `is_read`, `created_at`) VALUES
(1, 1, 'Stok Minimum', 'Stok barang Boots Kulit Coklat sudah mencapai batas minimum (8)', 'warning', 0, '2025-06-11 20:20:33'),
(2, 3, 'Stok Minimum', 'Stok barang Boots Kulit Coklat sudah mencapai batas minimum (8)', 'warning', 0, '2025-06-11 20:20:33'),
(9, 3, 'stok abis', 'woy isi amunisi', 'warning', 0, '2025-06-15 17:32:28'),
(10, 3, 'Stok Minimum', 'Stok barang Swaylow sudah mencapai batas minimum (5)', 'warning', 0, '2025-06-23 16:30:32');

-- --------------------------------------------------------

--
-- Struktur dari tabel `retur_barang`
--

CREATE TABLE `retur_barang` (
  `retur_id` int(11) NOT NULL,
  `nomor_retur` varchar(30) NOT NULL,
  `distributor_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `barang_id` int(11) DEFAULT NULL,
  `kode_barang` varchar(50) DEFAULT NULL,
  `nama_barang` varchar(255) DEFAULT NULL,
  `tanggal_retur` datetime NOT NULL,
  `alasan_retur` enum('tidak_laku','rusak','salah_kirim','expired','lainnya') DEFAULT 'tidak_laku',
  `jumlah` int(100) NOT NULL,
  `total_nilai_retur` decimal(15,2) DEFAULT 0.00,
  `status_retur` enum('pending','approved','rejected','shipped','completed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `retur_barang`
--

INSERT INTO `retur_barang` (`retur_id`, `nomor_retur`, `distributor_id`, `user_id`, `barang_id`, `kode_barang`, `nama_barang`, `tanggal_retur`, `alasan_retur`, `jumlah`, `total_nilai_retur`, `status_retur`, `created_at`, `updated_at`) VALUES
(1, '12kjk123', 2, 2, 4, 'BTS001', 'sepatu boots', '2025-06-13 13:31:16', 'rusak', 10, 6500000.00, 'pending', '2025-06-13 11:33:02', '2025-06-13 13:47:15'),
(3, '', NULL, NULL, 1, NULL, NULL, '2025-06-13 00:00:00', 'rusak', 3, 0.00, 'pending', '2025-06-13 13:47:29', '2025-06-13 13:47:29');

-- --------------------------------------------------------

--
-- Struktur dari tabel `trend_analysis`
--

CREATE TABLE `trend_analysis` (
  `trend_id` int(11) NOT NULL,
  `barang_id` int(11) DEFAULT NULL,
  `bulan` int(11) NOT NULL,
  `tahun` int(11) NOT NULL,
  `total_terjual` int(11) DEFAULT 0,
  `total_retur` int(11) DEFAULT 0,
  `persentase_retur` decimal(5,2) DEFAULT 0.00,
  `status_trend` enum('naik','turun','stabil','retur_tinggi') DEFAULT 'stabil',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `role` enum('admin-db','admin-wb','staff','owner') NOT NULL DEFAULT 'staff',
  `no_telp` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `email`, `nama_lengkap`, `role`, `no_telp`, `alamat`, `last_login`, `created_at`, `updated_at`, `is_active`) VALUES
(1, 'admin', '0192023a7bbd73250516f069df18b500', 'admin@shoesizzle.com', 'Administrator', 'admin-wb', '081234567890', NULL, NULL, '2025-06-11 10:42:13', '2025-06-15 09:14:37', 1),
(2, 'staff1', 'de9bf5643eabf80f4a56fda3bbb84483', 'staff1@shoesizzle.com', 'Staff Gudang 1', 'staff', '081234567891', NULL, NULL, '2025-06-11 10:42:13', '2025-06-11 10:42:13', 1),
(3, 'owner', '5be057accb25758101fa5eadbbd79503', 'owner@shoesizzle.com', 'Owner ShoeSizzle', 'owner', '081234567892', NULL, NULL, '2025-06-11 10:42:13', '2025-06-11 10:42:13', 1),
(4, 'admin2', 'admin089', 'admin2@gmail.com', 'admin2dong', 'admin-db', '089123456789', 'jonggol', NULL, '2025-06-13 10:08:32', '2025-06-15 09:14:48', 1);

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `v_inventory_report`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `v_inventory_report` (
`kode_barang` varchar(20)
,`nama_barang` varchar(100)
,`nama_kategori` varchar(50)
,`nama_merek` varchar(50)
,`stok_aktual` int(11)
,`stok_minimum` int(11)
,`harga_beli` decimal(12,2)
,`harga_jual` decimal(12,2)
,`nilai_stok` decimal(22,2)
,`status_stok` varchar(12)
);

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `v_return_report`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `v_return_report` (
`nomor_retur` varchar(30)
,`tanggal_retur` datetime
,`nama_distributor` varchar(100)
,`alasan_retur` enum('tidak_laku','rusak','salah_kirim','expired','lainnya')
,`status_retur` enum('pending','approved','rejected','shipped','completed')
,`total_qty` decimal(32,0)
,`total_nilai_retur` decimal(15,2)
);

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `v_sales_report`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `v_sales_report` (
`tanggal` date
,`nama_barang` varchar(100)
,`total_terjual` decimal(32,0)
,`total_penjualan` decimal(37,2)
);

-- --------------------------------------------------------

--
-- Struktur untuk view `v_inventory_report`
--
DROP TABLE IF EXISTS `v_inventory_report`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_inventory_report`  AS SELECT `b`.`kode_barang` AS `kode_barang`, `b`.`nama_barang` AS `nama_barang`, `k`.`nama_kategori` AS `nama_kategori`, `m`.`nama_merek` AS `nama_merek`, `b`.`stok_aktual` AS `stok_aktual`, `b`.`stok_minimum` AS `stok_minimum`, `b`.`harga_beli` AS `harga_beli`, `b`.`harga_jual` AS `harga_jual`, `b`.`stok_aktual`* `b`.`harga_beli` AS `nilai_stok`, CASE WHEN `b`.`stok_aktual` <= `b`.`stok_minimum` THEN 'Stok Minimum' WHEN `b`.`stok_aktual` = 0 THEN 'Habis' ELSE 'Normal' END AS `status_stok` FROM ((`barang` `b` left join `kategori` `k` on(`b`.`kategori_id` = `k`.`kategori_id`)) left join `merek` `m` on(`b`.`merek_id` = `m`.`merek_id`)) WHERE `b`.`is_active` = 1 ;

-- --------------------------------------------------------

--
-- Struktur untuk view `v_return_report`
--
DROP TABLE IF EXISTS `v_return_report`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_return_report`  AS SELECT `rb`.`nomor_retur` AS `nomor_retur`, `rb`.`tanggal_retur` AS `tanggal_retur`, `d`.`nama_distributor` AS `nama_distributor`, `rb`.`alasan_retur` AS `alasan_retur`, `rb`.`status_retur` AS `status_retur`, sum(`drb`.`jumlah_retur`) AS `total_qty`, `rb`.`total_nilai_retur` AS `total_nilai_retur` FROM ((`retur_barang` `rb` join `distributor` `d` on(`rb`.`distributor_id` = `d`.`distributor_id`)) join `detail_retur_barang` `drb` on(`rb`.`retur_id` = `drb`.`retur_id`)) GROUP BY `rb`.`retur_id` ;

-- --------------------------------------------------------

--
-- Struktur untuk view `v_sales_report`
--
DROP TABLE IF EXISTS `v_sales_report`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_sales_report`  AS SELECT cast(`bk`.`tanggal_keluar` as date) AS `tanggal`, `b`.`nama_barang` AS `nama_barang`, sum(`dbk`.`jumlah`) AS `total_terjual`, sum(`dbk`.`subtotal`) AS `total_penjualan` FROM ((`barang_keluar` `bk` join `detail_barang_keluar` `dbk` on(`bk`.`keluar_id` = `dbk`.`keluar_id`)) join `barang` `b` on(`dbk`.`barang_id` = `b`.`barang_id`)) WHERE `bk`.`jenis_keluar` = 'penjualan' GROUP BY cast(`bk`.`tanggal_keluar` as date), `b`.`barang_id` ;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `barang`
--
ALTER TABLE `barang`
  ADD PRIMARY KEY (`barang_id`),
  ADD UNIQUE KEY `kode_barang` (`kode_barang`),
  ADD KEY `idx_barang_kode` (`kode_barang`),
  ADD KEY `idx_barang_nama` (`nama_barang`),
  ADD KEY `idx_barang_kategori` (`kategori_id`),
  ADD KEY `idx_barang_merek` (`merek_id`);

--
-- Indeks untuk tabel `barang_keluar`
--
ALTER TABLE `barang_keluar`
  ADD PRIMARY KEY (`keluar_id`),
  ADD UNIQUE KEY `nomor_transaksi` (`nomor_transaksi`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_keluar_tanggal` (`tanggal_keluar`),
  ADD KEY `fk_barang_keluar` (`barang_id`);

--
-- Indeks untuk tabel `barang_masuk`
--
ALTER TABLE `barang_masuk`
  ADD PRIMARY KEY (`masuk_id`),
  ADD UNIQUE KEY `nomor_transaksi` (`nomor_transaksi`),
  ADD KEY `distributor_id` (`distributor_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_masuk_tanggal` (`tanggal_masuk`),
  ADD KEY `fk_barang` (`barang_id`);

--
-- Indeks untuk tabel `detail_barang_keluar`
--
ALTER TABLE `detail_barang_keluar`
  ADD PRIMARY KEY (`detail_keluar_id`),
  ADD KEY `keluar_id` (`keluar_id`),
  ADD KEY `barang_id` (`barang_id`);

--
-- Indeks untuk tabel `detail_barang_masuk`
--
ALTER TABLE `detail_barang_masuk`
  ADD PRIMARY KEY (`detail_masuk_id`),
  ADD KEY `masuk_id` (`masuk_id`),
  ADD KEY `barang_id` (`barang_id`);

--
-- Indeks untuk tabel `detail_retur_barang`
--
ALTER TABLE `detail_retur_barang`
  ADD PRIMARY KEY (`detail_retur_id`),
  ADD KEY `retur_id` (`retur_id`),
  ADD KEY `barang_id` (`barang_id`);

--
-- Indeks untuk tabel `distributor`
--
ALTER TABLE `distributor`
  ADD PRIMARY KEY (`distributor_id`);

--
-- Indeks untuk tabel `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`kategori_id`);

--
-- Indeks untuk tabel `laporan_stok`
--
ALTER TABLE `laporan_stok`
  ADD PRIMARY KEY (`laporan_id`),
  ADD KEY `barang_id` (`barang_id`);

--
-- Indeks untuk tabel `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `merek`
--
ALTER TABLE `merek`
  ADD PRIMARY KEY (`merek_id`);

--
-- Indeks untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD PRIMARY KEY (`notifikasi_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `retur_barang`
--
ALTER TABLE `retur_barang`
  ADD PRIMARY KEY (`retur_id`),
  ADD UNIQUE KEY `nomor_retur` (`nomor_retur`),
  ADD KEY `distributor_id` (`distributor_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_retur_tanggal` (`tanggal_retur`),
  ADD KEY `idx_retur_status` (`status_retur`),
  ADD KEY `fk_barang_retur` (`barang_id`);

--
-- Indeks untuk tabel `trend_analysis`
--
ALTER TABLE `trend_analysis`
  ADD PRIMARY KEY (`trend_id`),
  ADD UNIQUE KEY `unique_trend` (`barang_id`,`bulan`,`tahun`),
  ADD KEY `idx_trend_periode` (`tahun`,`bulan`),
  ADD KEY `idx_trend_status` (`status_trend`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `barang`
--
ALTER TABLE `barang`
  MODIFY `barang_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `barang_keluar`
--
ALTER TABLE `barang_keluar`
  MODIFY `keluar_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `barang_masuk`
--
ALTER TABLE `barang_masuk`
  MODIFY `masuk_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `detail_barang_keluar`
--
ALTER TABLE `detail_barang_keluar`
  MODIFY `detail_keluar_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `detail_barang_masuk`
--
ALTER TABLE `detail_barang_masuk`
  MODIFY `detail_masuk_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `detail_retur_barang`
--
ALTER TABLE `detail_retur_barang`
  MODIFY `detail_retur_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `distributor`
--
ALTER TABLE `distributor`
  MODIFY `distributor_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `kategori`
--
ALTER TABLE `kategori`
  MODIFY `kategori_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `laporan_stok`
--
ALTER TABLE `laporan_stok`
  MODIFY `laporan_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT untuk tabel `merek`
--
ALTER TABLE `merek`
  MODIFY `merek_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  MODIFY `notifikasi_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `retur_barang`
--
ALTER TABLE `retur_barang`
  MODIFY `retur_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `trend_analysis`
--
ALTER TABLE `trend_analysis`
  MODIFY `trend_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `barang`
--
ALTER TABLE `barang`
  ADD CONSTRAINT `barang_ibfk_1` FOREIGN KEY (`kategori_id`) REFERENCES `kategori` (`kategori_id`),
  ADD CONSTRAINT `barang_ibfk_2` FOREIGN KEY (`merek_id`) REFERENCES `merek` (`merek_id`);

--
-- Ketidakleluasaan untuk tabel `barang_keluar`
--
ALTER TABLE `barang_keluar`
  ADD CONSTRAINT `barang_keluar_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `fk_barang_keluar` FOREIGN KEY (`barang_id`) REFERENCES `barang` (`barang_id`);

--
-- Ketidakleluasaan untuk tabel `barang_masuk`
--
ALTER TABLE `barang_masuk`
  ADD CONSTRAINT `barang_masuk_ibfk_1` FOREIGN KEY (`distributor_id`) REFERENCES `distributor` (`distributor_id`),
  ADD CONSTRAINT `barang_masuk_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `fk_barang` FOREIGN KEY (`barang_id`) REFERENCES `barang` (`barang_id`);

--
-- Ketidakleluasaan untuk tabel `detail_barang_keluar`
--
ALTER TABLE `detail_barang_keluar`
  ADD CONSTRAINT `detail_barang_keluar_ibfk_1` FOREIGN KEY (`keluar_id`) REFERENCES `barang_keluar` (`keluar_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detail_barang_keluar_ibfk_2` FOREIGN KEY (`barang_id`) REFERENCES `barang` (`barang_id`);

--
-- Ketidakleluasaan untuk tabel `detail_barang_masuk`
--
ALTER TABLE `detail_barang_masuk`
  ADD CONSTRAINT `detail_barang_masuk_ibfk_1` FOREIGN KEY (`masuk_id`) REFERENCES `barang_masuk` (`masuk_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detail_barang_masuk_ibfk_2` FOREIGN KEY (`barang_id`) REFERENCES `barang` (`barang_id`);

--
-- Ketidakleluasaan untuk tabel `detail_retur_barang`
--
ALTER TABLE `detail_retur_barang`
  ADD CONSTRAINT `detail_retur_barang_ibfk_1` FOREIGN KEY (`retur_id`) REFERENCES `retur_barang` (`retur_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detail_retur_barang_ibfk_2` FOREIGN KEY (`barang_id`) REFERENCES `barang` (`barang_id`);

--
-- Ketidakleluasaan untuk tabel `laporan_stok`
--
ALTER TABLE `laporan_stok`
  ADD CONSTRAINT `laporan_stok_ibfk_1` FOREIGN KEY (`barang_id`) REFERENCES `barang` (`barang_id`);

--
-- Ketidakleluasaan untuk tabel `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  ADD CONSTRAINT `log_aktivitas_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD CONSTRAINT `notifikasi_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Ketidakleluasaan untuk tabel `retur_barang`
--
ALTER TABLE `retur_barang`
  ADD CONSTRAINT `fk_barang_retur` FOREIGN KEY (`barang_id`) REFERENCES `barang` (`barang_id`),
  ADD CONSTRAINT `retur_barang_ibfk_1` FOREIGN KEY (`distributor_id`) REFERENCES `distributor` (`distributor_id`),
  ADD CONSTRAINT `retur_barang_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Ketidakleluasaan untuk tabel `trend_analysis`
--
ALTER TABLE `trend_analysis`
  ADD CONSTRAINT `trend_analysis_ibfk_1` FOREIGN KEY (`barang_id`) REFERENCES `barang` (`barang_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
