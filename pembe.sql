-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 20, 2026 at 04:56 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pembe`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) DEFAULT 'admin',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `nama`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Administrator', 'admin@pembe.com', '$2y$10$b/jGvkKO3vlQ3O5nBwpWruglMOSUdbbLcIuBhHO.fpupVoLxWeBnO', 'super_admin', '2026-04-01 03:17:23');

-- --------------------------------------------------------

--
-- Table structure for table `daftar_ulang`
--

CREATE TABLE `daftar_ulang` (
  `id` int(11) NOT NULL,
  `id_peserta` int(11) NOT NULL,
  `nama_lengkap` varchar(100) DEFAULT NULL,
  `jurusan` varchar(100) DEFAULT NULL,
  `no_telepon` varchar(20) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `jenis_kelamin` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `nik` varchar(20) DEFAULT NULL,
  `tempat_lahir` varchar(100) DEFAULT NULL,
  `agama` varchar(20) DEFAULT NULL,
  `nama_ayah` varchar(100) DEFAULT NULL,
  `nama_ibu` varchar(100) DEFAULT NULL,
  `pekerjaan_ayah` varchar(100) DEFAULT NULL,
  `penghasilan_ortu` varchar(50) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `ktp` varchar(255) DEFAULT NULL,
  `nilai_test` int(11) DEFAULT NULL,
  `nim` varchar(20) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `tanggal_daftar_ulang` datetime DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `file_ijazah` varchar(255) DEFAULT NULL,
  `file_ktp` varchar(255) DEFAULT NULL,
  `ijazah` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `daftar_ulang`
--

INSERT INTO `daftar_ulang` (`id`, `id_peserta`, `nama_lengkap`, `jurusan`, `no_telepon`, `tanggal_lahir`, `jenis_kelamin`, `alamat`, `email`, `nik`, `tempat_lahir`, `agama`, `nama_ayah`, `nama_ibu`, `pekerjaan_ayah`, `penghasilan_ortu`, `foto`, `ktp`, `nilai_test`, `nim`, `status`, `tanggal_daftar_ulang`, `created_at`, `file_ijazah`, `file_ktp`, `ijazah`) VALUES
(3, 5, 'Jeno NCT', NULL, '0895365276118', '1999-12-12', 'Laki-laki', 'Korea Selatan from SM', 'jeno@gmail.com', '1234567891021112', 'Seoul', 'Islam', 'Siwon', 'Irene', 'CEO', '> 10.000.000', '1775318128_foto.jpg', '1775318128_ktp.jpg', 100, '2026SI00005', 'selesai', '2026-04-04 22:55:28', '2026-04-04 15:55:28', NULL, NULL, NULL),
(6, 7, 'Kirana Putri', NULL, '0895402693393', '2008-01-12', 'Perempuan', 'Jakarta Timur, Ciplak', 'kirana@gmail.com', '3121234578909876', 'Jakarta', 'Islam', 'Himawan', 'Fitri', 'PNS', '> 10.000.000', '1775450493_foto.jpeg', '1775450493_ktp.jpeg', 100, '2026TI00007', 'selesai', '2026-04-06 11:41:33', '2026-04-06 04:41:33', NULL, NULL, '1775450493_ijazah.jpeg'),
(7, 8, 'Nadinda Najuwa', NULL, '0895365276118', '2008-05-08', 'Perempuan', 'JL. Otista 3 Dalam Jakarta Timur', 'nadinda@gmail.com', '3123454657890988', 'Jakarta', 'Islam', 'Bahlih', 'Yuli', 'CEO', '> 10.000.000', '1775456485_foto.png', '1775456485_ktp.jpg', 100, '2026TI00008', 'pending', '2026-04-06 13:21:25', '2026-04-06 06:21:25', NULL, NULL, '1775456485_ijazah.jpeg');

-- --------------------------------------------------------

--
-- Table structure for table `hasil_test`
--

CREATE TABLE `hasil_test` (
  `id` int(11) NOT NULL,
  `id_peserta` int(11) NOT NULL,
  `nama_peserta` varchar(100) DEFAULT NULL,
  `jurusan` varchar(50) DEFAULT NULL,
  `jawaban_benar` int(11) DEFAULT 0,
  `total_soal` int(11) DEFAULT 0,
  `total_nilai` int(11) DEFAULT 0,
  `jumlah_benar` int(11) DEFAULT 0,
  `jumlah_salah` int(11) DEFAULT 0,
  `persentase` decimal(5,2) DEFAULT 0.00,
  `status` varchar(20) DEFAULT 'proses',
  `tanggal_test` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hasil_test`
--

INSERT INTO `hasil_test` (`id`, `id_peserta`, `nama_peserta`, `jurusan`, `jawaban_benar`, `total_soal`, `total_nilai`, `jumlah_benar`, `jumlah_salah`, `persentase`, `status`, `tanggal_test`) VALUES
(12, 5, 'Jeno NCT', 'Sistem Informasi', 0, 0, 100, 10, 0, 100.00, 'lulus', '2026-04-04 15:03:16'),
(14, 7, 'Kirana Putri', 'Teknik Informatika', 0, 0, 100, 10, 0, 100.00, 'lulus', '2026-04-06 04:28:54'),
(15, 8, 'Nadinda Najuwa', 'Teknik Informatika', 0, 0, 100, 10, 0, 100.00, 'lulus', '2026-04-06 06:19:25');

-- --------------------------------------------------------

--
-- Table structure for table `jawaban_user`
--

CREATE TABLE `jawaban_user` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `soal_id` int(11) DEFAULT NULL,
  `jawaban` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jawaban_user`
--

INSERT INTO `jawaban_user` (`id`, `user_id`, `soal_id`, `jawaban`) VALUES
(1, 9, 1, 'biru putih'),
(2, 10, 1, 'Biru putih'),
(3, 11, 1, 'biru'),
(4, 11, 2, '22'),
(5, 13, 1, '2'),
(6, 18, 1, 'a'),
(7, 19, 1, 'b'),
(8, 19, 2, 'b'),
(9, 20, 1, 'a'),
(10, 20, 2, 'b'),
(11, 21, 1, 'a'),
(12, 21, 2, 'a'),
(13, 22, 1, 'a'),
(14, 22, 2, 'b'),
(15, 23, 7, 'b'),
(16, 24, 7, 'b'),
(17, 25, 7, 'b'),
(18, 26, 7, 'b'),
(19, 27, 7, 'b');

-- --------------------------------------------------------

--
-- Table structure for table `peserta`
--

CREATE TABLE `peserta` (
  `id` int(11) NOT NULL,
  `nama_lengkap` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `no_telepon` varchar(20) DEFAULT NULL,
  `jurusan` varchar(50) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'terdaftar'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ranking`
--

CREATE TABLE `ranking` (
  `id` int(11) NOT NULL,
  `id_peserta` int(11) NOT NULL,
  `nama_lengkap` varchar(100) DEFAULT NULL,
  `jurusan` varchar(100) DEFAULT NULL,
  `jumlah_benar` int(11) DEFAULT 0,
  `jumlah_salah` int(11) DEFAULT 0,
  `total_nilai` int(11) DEFAULT 0,
  `predikat` varchar(50) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `tanggal_test` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `soal`
--

CREATE TABLE `soal` (
  `id` int(11) NOT NULL,
  `jurusan` varchar(50) DEFAULT NULL,
  `pertanyaan` text NOT NULL,
  `pilihan_a` varchar(255) NOT NULL,
  `pilihan_b` varchar(255) NOT NULL,
  `pilihan_c` varchar(255) NOT NULL,
  `pilihan_d` varchar(255) NOT NULL,
  `jawaban_benar` varchar(1) NOT NULL,
  `bobot` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `soal`
--

INSERT INTO `soal` (`id`, `jurusan`, `pertanyaan`, `pilihan_a`, `pilihan_b`, `pilihan_c`, `pilihan_d`, `jawaban_benar`, `bobot`, `created_at`) VALUES
(7, 'Teknik Informatika', 'RPL adalah singkatan dari?\r\n', 'Rekayasa Perangkat Lunak', 'Rakit Perangkat Lunak', ' Rencana Perangkat Lunak', ' Rekap Perangkat Lunak', 'A', 10, '2026-04-01 04:41:49'),
(8, 'Teknik Informatika', 'Software gratis disebut?', 'Premium', 'Freeware', 'Trial', 'Hotspot', 'B', 10, '2026-04-01 05:09:26'),
(9, 'Teknik Informatika', 'Apa yang di maksud dengan User?', 'Server', 'Admin', 'Pengguna sistem', 'Pembuat program', 'C', 10, '2026-04-01 05:11:35'),
(10, 'Teknik Informatika', 'Apa yang di maksud dengan admin?', 'Penonton', 'Pengguna biasa', 'Pembeli', 'Pengelola sistem', 'D', 10, '2026-04-01 05:12:58'),
(16, 'Sistem Informasi', 'PHP digunakan untuk apa?', ' Backend web', 'Desain gambar', 'Editing video', 'Musik', 'A', 1, '2026-04-01 12:54:02'),
(17, 'Teknik Informatika', 'Apa fungsi dari coding?', 'Membuat program ', 'Menggambar ', 'Menghapus data', 'Membaca file', 'A', 1, '2026-04-04 13:48:53'),
(18, 'Teknik Informatika', 'Apa yang di maksud dengn admin?', 'Pengguna biasa', 'Pengelola sistem', 'Pembeli', 'Penonton', 'B', 1, '2026-04-04 13:50:24'),
(19, 'Teknik Informatika', 'Username berfungsi sebagai?', 'Password', 'Server', 'Identitas pengguna', 'Database', 'C', 1, '2026-04-04 13:52:24'),
(20, 'Teknik Informatika', 'Login digunakan untuk?', 'Keluar dari sistem', 'Menyimpan file', ' Menghapus data', 'Masuk ke sistem', 'D', 1, '2026-04-04 13:53:55'),
(21, 'Teknik Informatika', 'Contoh dari browser adalah?', 'Chrome', 'Word ', 'Excel ', 'TikTok', 'A', 1, '2026-04-04 13:56:31'),
(22, 'Teknik Informatika', 'XAMPP digunakan untuk?', ' Bermain game', ' Menjalankan server lokal', 'Mengedit video', 'Menonton film', 'B', 1, '2026-04-04 13:58:04'),
(23, 'Sistem Informasi', 'CSS digunakan untuk apa?', 'Logika program', 'Mempercantik tampilan', 'Database ', 'Server', 'B', 1, '2026-04-04 13:59:58'),
(24, 'Sistem Informasi', 'RPL adalah singkatan dari?', 'Rakit Perangkat Lunak', ' Rencana Perangkat Lunak', ' Rekayasa Perangkat Lunak', 'Rekap Perangkat Lunak', 'C', 1, '2026-04-04 14:01:33'),
(25, 'Sistem Informasi', 'RPL adalah singkatan dari?', 'Rakit Perangkat Lunak', ' Rencana Perangkat Lunak', ' Rekap Perangkat Lunak', 'Rekayasa Perangkat Lunak', 'D', 1, '2026-04-04 14:01:33'),
(26, 'Sistem Informasi', 'Aplikasi apa yang berguna untuk membuat database?', 'MySQL', 'Word', 'Spotify', 'TikTok', 'A', 1, '2026-04-04 14:07:30'),
(27, 'Sistem Informasi', 'Perawatan software disebut?', 'Design', 'Maintenance', 'Coding', 'Testing', 'B', 1, '2026-04-04 14:11:53'),
(28, 'Sistem Informasi', 'Testing bertujuan untuk?', ' Menghapus data', 'Membuat gambar', 'Mencari kesalahan program', 'Mencari kesalahan mantan', 'C', 1, '2026-04-04 14:13:12'),
(29, 'Sistem Informasi', 'Apa contoh bahasa pemrograman?', 'Chrome', 'Linux', 'YouTube', 'HTML', 'D', 1, '2026-04-04 14:15:34'),
(30, 'Sistem Informasi', 'Bahasa pemrograman digunakan untuk?', ' Membuat program', 'Menggambar', 'Mendengarkan musik', 'Mendengarkan musik', 'A', 1, '2026-04-04 14:18:13'),
(31, 'Sistem Informasi', 'Orang yang membuat program disebut?', ' User', 'Programmer', 'Operator ', 'Admin ', 'B', 1, '2026-04-04 14:19:36');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` varchar(20) DEFAULT NULL,
  `no_test` varchar(20) DEFAULT NULL,
  `status_test` varchar(20) DEFAULT NULL,
  `nim` varchar(20) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `nama_lengkap` varchar(100) DEFAULT NULL,
  `no_telp` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `jurusan` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `role`, `no_test`, `status_test`, `nim`, `foto`, `nama_lengkap`, `no_telp`, `alamat`, `jurusan`) VALUES
(1, 'admin@pembe.com', 'admin123', 'admin', NULL, NULL, NULL, NULL, '', '', NULL, NULL),
(5, 'jeno@gmail.com', '$2y$10$LnGuN8dzBbfmm09GyffG8.Zb/SRX6MPNqUez2Dz7TDbE7fgUWQzl.', 'user', 'PMB20265056', 'lulus', '2026010001', 'uploads/1775389605_foto.jpg', 'Jeno NCT', '0895365276118', 'Korea Sselatan from SM', 'Sistem Informasi'),
(7, 'kirana@gmail.com', '$2y$10$68GvZ7rk2zb3OZzZp7C7S.WSRnOCJuLPR7GN26qf5mdOQJkpnkTaC', 'user', 'PMB20264114', 'lulus', NULL, 'uploads/1775455663_WhatsAppImage2026-01-25at15.08.34.jpeg', 'Kirana Putri', '0895402693393', 'Jakarta Timur, Ciplak', 'Teknik Informatika'),
(8, 'nadinda@gmail.com', '$2y$10$4FKnwerspp6nteFGuwvg.ew5KkxmfKyLxCV4rZpBoEzzRdI01gZVa', 'user', 'PMB20269287', 'lulus', NULL, NULL, 'Nadinda Najuwa', '0895365276118', 'JL. Otista 3 Dalam Jakarta Timur', 'Teknik Informatika');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `daftar_ulang`
--
ALTER TABLE `daftar_ulang`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hasil_test`
--
ALTER TABLE `hasil_test`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `jawaban_user`
--
ALTER TABLE `jawaban_user`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `peserta`
--
ALTER TABLE `peserta`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ranking`
--
ALTER TABLE `ranking`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `soal`
--
ALTER TABLE `soal`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `daftar_ulang`
--
ALTER TABLE `daftar_ulang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `hasil_test`
--
ALTER TABLE `hasil_test`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `jawaban_user`
--
ALTER TABLE `jawaban_user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `peserta`
--
ALTER TABLE `peserta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `ranking`
--
ALTER TABLE `ranking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `soal`
--
ALTER TABLE `soal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
