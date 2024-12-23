<?php
session_start();
require '../../koneksi.php'; // Koneksi ke database
require '../../fpdf/fpdf.php'; // Library FPDF

// Periksa apakah pengguna sudah login
if (!isset($_SESSION['user'])) {
    header("Location: ../../index.php");
    exit;
}

$user = $_SESSION['user'];

// Query untuk mendapatkan semua field dari tabel mahasiswa berdasarkan user_id
$query = "SELECT * FROM mahasiswa WHERE user_id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$user['user_id']]);
$data_mahasiswa = $stmt->fetch(PDO::FETCH_ASSOC);

// Periksa apakah data ditemukan
if (!$data_mahasiswa) {
    echo "Data mahasiswa tidak ditemukan!";
    exit;
}

// Membuat objek PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);

// Judul
$pdf->Cell(0, 10, 'Data Calon Mahasiswa', 0, 1, 'C');
$pdf->Cell(0, 10, 'Universitas IPWIJA', 0, 1, 'C');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, 'Alamat:  Jl. Letda Nasir No.7, Nagrak, Kec. Gn. Putri, Kabupaten Bogor, Jawa Barat 16967', 0, 1, 'C');
$pdf->Cell(0, 10, 'Telp:  (021) 8233737 web:https://ipwija.ac.id/', 0, 1, 'C');

$pdf->Ln(10);

// Tambahkan informasi mahasiswa secara dinamis
$pdf->SetFont('Arial', '', 12);
$pdf->Ln(10);

foreach ($data_mahasiswa as $field => $value) {
    $pdf->Cell(50, 10, ucfirst(str_replace('_', ' ', $field)), 1); // Nama Field
    $pdf->Cell(140, 10, $value ?: 'Tidak ada', 1, 1); // Nilai Field
}

// Output PDF
$pdf->Output('D', 'data_mahasiswa.pdf'); // Mengunduh file
