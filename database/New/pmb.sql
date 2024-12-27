-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 27 Des 2024 pada 13.47
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
-- Database: `pmb`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `berkas`
--

CREATE TABLE `berkas` (
  `berkas_id` int(11) NOT NULL,
  `mahasiswa_id` int(11) NOT NULL,
  `jenis_berkas` varchar(100) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `upload_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `berkas`
--

INSERT INTO `berkas` (`berkas_id`, `mahasiswa_id`, `jenis_berkas`, `file_path`, `upload_time`) VALUES
(54, 86, 'PDF', '../../uploads/file_676ea022018887.44007306.pdf', '2024-12-27 12:40:02');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kelas`
--

CREATE TABLE `kelas` (
  `kelas_id` int(11) NOT NULL,
  `nama_kelas` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kelas`
--

INSERT INTO `kelas` (`kelas_id`, `nama_kelas`) VALUES
(1, 'Reguler'),
(2, 'Kelas Karyawan'),
(3, 'Rekognisi Pembelajaran Lampau (RPL)');

-- --------------------------------------------------------

--
-- Struktur dari tabel `mahasiswa`
--

CREATE TABLE `mahasiswa` (
  `mahasiswa_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `nik` char(16) NOT NULL,
  `alamat` text NOT NULL,
  `sekolah_asal` varchar(100) DEFAULT NULL,
  `tahun_lulus` year(4) NOT NULL,
  `biaya_pendaftaran` int(100) NOT NULL,
  `no_telp` varchar(15) DEFAULT NULL,
  `program_studi_id` int(11) NOT NULL,
  `kelas_id` int(11) NOT NULL,
  `Nilai_ujian` int(50) NOT NULL,
  `waktu_pendaftaran` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `mahasiswa`
--

INSERT INTO `mahasiswa` (`mahasiswa_id`, `user_id`, `nama_lengkap`, `nik`, `alamat`, `sekolah_asal`, `tahun_lulus`, `biaya_pendaftaran`, `no_telp`, `program_studi_id`, `kelas_id`, `Nilai_ujian`, `waktu_pendaftaran`) VALUES
(85, 31, 'STEVANUS ANDIKA GALIH SETIAWAN', '39939010333', 'JL SEPAKAT 4 NO.57 RT03 RW001', 'SMK BINA NUSA  MANDIRI', '2023', 1500000, '089604134028', 1, 2, 0, '2024-12-27 03:15:29'),
(86, 30, 'Steven', '482919393', 'JL 132939', 'SMK HUSNI THAMRIN', '2023', 1500000, '3839940', 1, 2, 99, '2024-12-27 12:40:42');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pendaftaran`
--

CREATE TABLE `pendaftaran` (
  `pendaftaran_id` int(11) NOT NULL,
  `mahasiswa_id` int(11) NOT NULL,
  `status` enum('menunggu disetujui','disetujui') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pendaftaran`
--

INSERT INTO `pendaftaran` (`pendaftaran_id`, `mahasiswa_id`, `status`) VALUES
(29, 86, 'menunggu disetujui');

-- --------------------------------------------------------

--
-- Struktur dari tabel `program_studi`
--

CREATE TABLE `program_studi` (
  `program_studi_id` int(11) NOT NULL,
  `nama_program_studi` varchar(100) NOT NULL,
  `status_akreditasi` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `program_studi`
--

INSERT INTO `program_studi` (`program_studi_id`, `nama_program_studi`, `status_akreditasi`) VALUES
(1, 'S1 Rekayasa Perangkat Lunak', 'Baik Sekali'),
(2, ' S1 Informatika', 'Baik Sekali'),
(3, 'S1 Sistem Informasi', 'Baik Sekali'),
(4, 'D3 Kebidanan', 'Baik Sekali'),
(5, 'S1 Kewirausahaan', 'Baik Sekali'),
(6, 'S2 Manajemen', 'Baik Sekali'),
(7, 'S1 Manajemen', 'Baik Sekali');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tanggal_pendaftaran`
--

CREATE TABLE `tanggal_pendaftaran` (
  `id_tanggal_daftar` int(5) NOT NULL,
  `Nama_Kegiatan` varchar(50) NOT NULL,
  `tanggaL_berakhir` date NOT NULL,
  `tanggal_daftar` date NOT NULL,
  `keterangan` enum('Belum Dimulai','Berlangsung','Selesai','Kadaluarsa') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tanggal_pendaftaran`
--

INSERT INTO `tanggal_pendaftaran` (`id_tanggal_daftar`, `Nama_Kegiatan`, `tanggaL_berakhir`, `tanggal_daftar`, `keterangan`) VALUES
(2, 'Mempersiapkan Dokumen Yang Dibutuhkan', '2024-12-20', '2024-12-16', 'Kadaluarsa'),
(3, 'Upload Berkas Dan Isi Biodata', '2025-01-01', '2024-12-23', 'Selesai'),
(4, 'UJIAN CBT', '2025-02-24', '2025-01-13', 'Belum Dimulai'),
(5, 'Proses Seleksi Berkas ', '2025-02-25', '2025-02-05', 'Belum Dimulai'),
(6, 'Pengumuman Seleksi', '2025-03-01', '2025-02-25', 'Belum Dimulai');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','pendaftar') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `jwt_token` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password`, `role`, `created_at`, `jwt_token`) VALUES
(30, 'Stevanus Andika Galih Setiawan', 'stevcomp58@gmail.com', '$2y$10$S9SCdFxxr12Z2BHHPZVfp.YVcEdEkniXsparyQZxpu.ZCQuuQePI2', 'pendaftar', '2024-12-23 13:37:57', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpYXQiOjE3MzQ5NjEwNzcsImV4cCI6MTczNDk2MTM3NywiZW1haWwiOiJzdGV2Y29tcDU4QGdtYWlsLmNvbSJ9.C7qEC7LklxNP0qDNqXJ6g4r3JZYEYydWsrdTTbPREmU'),
(31, 'Admin', 'admin@example.com', '$2y$10$uvEkx1BjtTy9ZAu0a/C15OoDtr3hrePA3aPBRDU3lzBpulN/5LxG6', 'admin', '2024-12-23 14:48:27', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpYXQiOjE3MzQ5NjUzMDcsImV4cCI6MTczNDk2NTYwNywiZW1haWwiOiJ1c2VyQGV4YW1wbGUuY29tIn0.CJCH3nznCR2oxXlVtJFoUrovoB-HZIXu85O3cvXMyKM');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `berkas`
--
ALTER TABLE `berkas`
  ADD PRIMARY KEY (`berkas_id`),
  ADD KEY `fk_mahasiswa_id` (`mahasiswa_id`);

--
-- Indeks untuk tabel `kelas`
--
ALTER TABLE `kelas`
  ADD PRIMARY KEY (`kelas_id`);

--
-- Indeks untuk tabel `mahasiswa`
--
ALTER TABLE `mahasiswa`
  ADD PRIMARY KEY (`mahasiswa_id`),
  ADD UNIQUE KEY `nik` (`nik`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `program_studi_id` (`program_studi_id`),
  ADD KEY `kelas_id` (`kelas_id`);

--
-- Indeks untuk tabel `pendaftaran`
--
ALTER TABLE `pendaftaran`
  ADD PRIMARY KEY (`pendaftaran_id`),
  ADD KEY `mahasiswa_id` (`mahasiswa_id`);

--
-- Indeks untuk tabel `program_studi`
--
ALTER TABLE `program_studi`
  ADD PRIMARY KEY (`program_studi_id`);

--
-- Indeks untuk tabel `tanggal_pendaftaran`
--
ALTER TABLE `tanggal_pendaftaran`
  ADD PRIMARY KEY (`id_tanggal_daftar`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `berkas`
--
ALTER TABLE `berkas`
  MODIFY `berkas_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT untuk tabel `kelas`
--
ALTER TABLE `kelas`
  MODIFY `kelas_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `mahasiswa`
--
ALTER TABLE `mahasiswa`
  MODIFY `mahasiswa_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=87;

--
-- AUTO_INCREMENT untuk tabel `pendaftaran`
--
ALTER TABLE `pendaftaran`
  MODIFY `pendaftaran_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT untuk tabel `program_studi`
--
ALTER TABLE `program_studi`
  MODIFY `program_studi_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `tanggal_pendaftaran`
--
ALTER TABLE `tanggal_pendaftaran`
  MODIFY `id_tanggal_daftar` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `berkas`
--
ALTER TABLE `berkas`
  ADD CONSTRAINT `berkas_ibfk_1` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswa` (`mahasiswa_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_mahasiswa_id` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswa` (`mahasiswa_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `mahasiswa`
--
ALTER TABLE `mahasiswa`
  ADD CONSTRAINT `mahasiswa_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mahasiswa_ibfk_2` FOREIGN KEY (`program_studi_id`) REFERENCES `program_studi` (`program_studi_id`),
  ADD CONSTRAINT `mahasiswa_ibfk_3` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`kelas_id`);

--
-- Ketidakleluasaan untuk tabel `pendaftaran`
--
ALTER TABLE `pendaftaran`
  ADD CONSTRAINT `pendaftaran_ibfk_1` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswa` (`mahasiswa_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
