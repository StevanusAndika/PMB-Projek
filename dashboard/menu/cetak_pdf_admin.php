<?php
session_start();
require '../../koneksi.php'; // Koneksi ke database
require '../../fpdf/fpdf.php'; // Library FPDF

// Periksa apakah pengguna sudah login
if (!isset($_SESSION['user'])) {
    header("Location: ../../index.php");
    exit;
}

// Periksa apakah ID mahasiswa tersedia di URL
if (!isset($_GET['id'])) {
    echo "ID mahasiswa tidak ditemukan!";
    exit;
}

$mahasiswa_id = $_GET['id'];

// Query untuk mendapatkan informasi mahasiswa dan gabungan tabel terkait
$query = "
    SELECT 
        m.*,
        ps.nama_program_studi AS program_studi,
        k.nama_kelas AS kelas,
        p.status AS status_pendaftaran,
        p.keterangan
    FROM mahasiswa m
    LEFT JOIN program_studi ps ON m.program_studi_id = ps.program_studi_id
    LEFT JOIN kelas k ON m.kelas_id = k.kelas_id
    LEFT JOIN pendaftaran p ON m.mahasiswa_id = p.mahasiswa_id
    WHERE m.mahasiswa_id = ?
";
$stmt = $pdo->prepare($query);
$stmt->execute([$mahasiswa_id]);
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
$pdf->Cell(0, 10, 'Alamat: Jl. Letda Nasir No.7, Nagrak, Kec. Gn. Putri, Kabupaten Bogor, Jawa Barat 16967', 0, 1, 'C');
$pdf->Cell(0, 10, 'Telp: (021) 8233737 Web: https://ipwija.ac.id/', 0, 1, 'C');

$pdf->Ln(10);

// Tambahkan informasi mahasiswa secara dinamis
$pdf->SetFont('Arial', '', 12);

// Data dinamis
$fields = [
    'Nama Lengkap' => $data_mahasiswa['nama_lengkap'],
    'NIK' => $data_mahasiswa['nik'],
    'Alamat' => $data_mahasiswa['alamat'],
    'Sekolah Asal' => $data_mahasiswa['sekolah_asal'] ?: 'Tidak ada',
    'Tahun Lulus' => $data_mahasiswa['tahun_lulus'] ?: 'Tidak ada',
    'Biaya Pendaftaran' => 'Rp. ' . number_format($data_mahasiswa['biaya_pendaftaran'], 0, ',', '.'),
    'No. Telepon' => $data_mahasiswa['no_telp'] ?: 'Tidak ada',
    'Program Studi' => $data_mahasiswa['program_studi'] ?: 'Tidak ada',
    'Kelas' => $data_mahasiswa['kelas'] ?: 'Tidak ada',
    'Gelombang' => $data_mahasiswa['Gelombang'] ?: 'Tidak ada',
    'Nilai Ujian' => $data_mahasiswa['Nilai_ujian'],
    'Status Pendaftaran' => ucfirst($data_mahasiswa['status_pendaftaran']) ?: 'Tidak ada',
    'Keterangan' => $data_mahasiswa['keterangan'] ?: 'Tidak ada', // Tambahkan keterangan
    'Waktu Pendaftaran' => $data_mahasiswa['waktu_pendaftaran'] ?: 'Tidak ada'
];

// Lebar kolom
$colWidthLabel = 70; // Lebar kolom label
$colWidthValue = 120; // Lebar kolom nilai

foreach ($fields as $label => $value) {
    // Ubah tinggi baris agar sesuai dengan teks yang panjang
    $lineHeight = 8; // Tinggi setiap baris
    
    // Tulis kolom label
    $pdf->Cell($colWidthLabel, $lineHeight, $label, 1); 
    
    // Tulis kolom nilai dengan teks yang dibungkus otomatis
    $pdf->MultiCell($colWidthValue, $lineHeight, $value, 1); 
}

// Output PDF
$pdf->Output('D', 'data_mahasiswa.pdf'); // Mengunduh file
?>

