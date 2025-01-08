<?php 
session_start();
include '../../koneksi.php'; // Menghubungkan ke database

// Periksa apakah pengguna sudah login
if (!isset($_SESSION['user'])) {
    header("Location: ../../index.php");
    exit;
}

$user = $_SESSION['user'];

// Query untuk mendapatkan role pengguna
$query = "SELECT role FROM users WHERE user_id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$user['user_id']]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

// Cek jika role bukan admin
if ($result['role'] !== 'admin') {
    echo "<h1>Anda tidak memiliki akses ke menu admin</h1>";
    exit;
}

// Periksa apakah ada id mahasiswa yang diterima
if (isset($_GET['id'])) {
    $mahasiswa_id = $_GET['id'];

    // Query untuk menghapus data mahasiswa berdasarkan mahasiswa_id
    $query = "DELETE FROM mahasiswa WHERE mahasiswa_id = ?";
    $stmt = $pdo->prepare($query);
    if ($stmt->execute([$mahasiswa_id])) {
        // Jika berhasil dihapus, alihkan ke halaman daftar mahasiswa
        header("Location: admin_liatdata.php?status=success");
        exit;
    } else {
        // Jika gagal, tampilkan pesan error
        echo "<h1>Gagal menghapus data mahasiswa.</h1>";
    }
} else {
    echo "<h1>Data tidak ditemukan.</h1>";
}
?>
