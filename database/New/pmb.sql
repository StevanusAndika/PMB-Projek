-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 15 Jan 2025 pada 07.25
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
(117, 144, 'Ijazah/Transkrip Nilai', '../../uploads/file_67865a0db000f8.49005612.pdf', '2025-01-14 12:35:25'),
(118, 147, 'Ijazah/Transkrip Nilai', '../../uploads/file_678753afe719e5.35441031.pdf', '2025-01-15 06:20:31');

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
  `waktu_pendaftaran` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `Gelombang` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `mahasiswa`
--

INSERT INTO `mahasiswa` (`mahasiswa_id`, `user_id`, `nama_lengkap`, `nik`, `alamat`, `sekolah_asal`, `tahun_lulus`, `biaya_pendaftaran`, `no_telp`, `program_studi_id`, `kelas_id`, `Nilai_ujian`, `waktu_pendaftaran`, `Gelombang`) VALUES
(144, 35, 'ARGI VAN MAULANA', '31239399128', 'JL CIANJUR NO.18', 'SMK husni thamrin', '2023', 9850000, '959595', 1, 2, 67, '2025-01-14 12:46:29', '1'),
(145, 36, 'Nur Khalida Farhatie', '317629199', 'Jalan By Pass Ngurah Rai No.71,Bali', 'SMAN 77 BOGOR', '2020', 7500000, '192939', 1, 2, 90, '2025-01-15 05:38:08', '2'),
(146, 37, 'Mirza', '317299', 'JALAN 810', 'SMAN 1 CIKARANG', '2019', 7500000, '087129399', 7, 2, 0, '2025-01-15 05:43:53', '2'),
(147, 30, 'STEVANUS ANDIKA GALIH SETIAWAN', '5858585844444444', 'JL SEPAKAT 4 NO.57 RT003 RW001,CILANGKAP,JAKARTA TIMUR', 'SMK BINA NUSA MANDIRI', '2023', 7500000, '959595', 1, 2, 0, '2025-01-15 06:21:23', '1');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pendaftaran`
--

CREATE TABLE `pendaftaran` (
  `pendaftaran_id` int(11) NOT NULL,
  `mahasiswa_id` int(11) NOT NULL,
  `status` enum('menunggu disetujui','disetujui','pending','ditolak') NOT NULL,
  `keterangan` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pendaftaran`
--

INSERT INTO `pendaftaran` (`pendaftaran_id`, `mahasiswa_id`, `status`, `keterangan`) VALUES
(85, 144, 'pending', 'DATA/BERKAS ANDA BELUM LENGKAP'),
(86, 145, 'menunggu disetujui', 'Menunggu Dikonfirmasi By Admin'),
(87, 146, 'ditolak', 'TIDAK MELUNASI BIAYA'),
(88, 147, 'disetujui', 'DATA ANDA TELAH LENGKAP');

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
  `keterangan` enum('Belum Dimulai','Berlangsung','Selesai') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tanggal_pendaftaran`
--

INSERT INTO `tanggal_pendaftaran` (`id_tanggal_daftar`, `Nama_Kegiatan`, `tanggaL_berakhir`, `tanggal_daftar`, `keterangan`) VALUES
(2, 'Mempersiapkan Dokumen Yang Dibutuhkan', '2024-12-20', '2024-12-16', ''),
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
(30, 'Steven', 'user@example.com', '$2y$10$S9SCdFxxr12Z2BHHPZVfp.YVcEdEkniXsparyQZxpu.ZCQuuQePI2', 'pendaftar', '2024-12-23 13:37:57', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpYXQiOjE3MzQ5NjEwNzcsImV4cCI6MTczNDk2MTM3NywiZW1haWwiOiJzdGV2Y29tcDU4QGdtYWlsLmNvbSJ9.C7qEC7LklxNP0qDNqXJ6g4r3JZYEYydWsrdTTbPREmU'),
(31, 'Admin', 'admin@example.com', '$2y$10$ahoiGKaxVKdlYVBo.t7XRucbltoYVhqUhEZVbFdZLQjICA8EBQvHi', 'admin', '2024-12-23 14:48:27', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpYXQiOjE3MzQ5NjUzMDcsImV4cCI6MTczNDk2NTYwNywiZW1haWwiOiJ1c2VyQGV4YW1wbGUuY29tIn0.CJCH3nznCR2oxXlVtJFoUrovoB-HZIXu85O3cvXMyKM'),
(35, 'Argi', 'argi@mail.com', '$2y$10$zuW7O8JcRWbgjL.J0N03kOVwwcSkkm48PTEVeJCBIIORKkcL2QNcy', 'pendaftar', '2025-01-14 12:32:57', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpYXQiOjE3MzY4NTc5NzcsImV4cCI6MTczNjg1ODI3NywiZW1haWwiOiJhcmdpQG1haWwuY29tIn0.9N-ZFPGPCyX4Yr_0JnRc4So_3wFQjFBI22iuIgOf6Vo'),
(36, 'Nur Khalida Farhatie', 'Nur01@gmail.com', '$2y$10$RMm30rvm0tjxRp5ECDQ8ru0cLgKbVJAdnW4GvxLdI65/ddayZwv4i', 'pendaftar', '2025-01-15 05:19:48', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpYXQiOjE3MzY5MTgzODgsImV4cCI6MTczNjkxODY4OCwiZW1haWwiOiJOdXIwMUBnbWFpbC5jb20ifQ.ZVek_SG1-uKv_ex9qLwhWyMR1JhB5y9AZxqCkWjCvvo'),
(37, 'Mirza', 'mirza@mail.com', '$2y$10$atsayX8iuu2nNkbHhBh9Au9Mx5RJcVBOQUVYYssE43Uk5c2bfmzcC', 'pendaftar', '2025-01-15 05:41:02', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpYXQiOjE3MzY5MTk2NjIsImV4cCI6MTczNjkxOTk2MiwiZW1haWwiOiJtaXJ6YUBtYWlsLmNvbSJ9.kVakecmVfnY4XH4tkBSnwDUqSTTiq7oFlnXOKYToFB4');

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
  MODIFY `berkas_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=119;

--
-- AUTO_INCREMENT untuk tabel `kelas`
--
ALTER TABLE `kelas`
  MODIFY `kelas_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `mahasiswa`
--
ALTER TABLE `mahasiswa`
  MODIFY `mahasiswa_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=148;

--
-- AUTO_INCREMENT untuk tabel `pendaftaran`
--
ALTER TABLE `pendaftaran`
  MODIFY `pendaftaran_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

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
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

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
