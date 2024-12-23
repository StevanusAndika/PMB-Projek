<?php
// Menghubungkan ke database menggunakan PDO
session_start();
include '../../koneksi.php';

// Pastikan user sudah login
if (!isset($_SESSION['user'])) {
    echo "User not logged in.";
    exit;
}

// Cek apakah id mahasiswa ada di URL
if (isset($_GET['id'])) {
    $mahasiswa_id = $_GET['id'];

    // Cek jika mahasiswa_id valid
    if (!empty($mahasiswa_id)) {
        try {
            // Hapus data mahasiswa dari database
            $stmt = $pdo->prepare("DELETE FROM mahasiswa WHERE mahasiswa_id = :mahasiswa_id");
            $stmt->execute([':mahasiswa_id' => $mahasiswa_id]);

            // Menampilkan pesan jika data berhasil dihapus
            $message = 'Data mahasiswa berhasil dihapus.';
            $status = 'success';
        } catch (PDOException $e) {
            // Menampilkan pesan error jika gagal menghapus
            $message = 'Gagal menghapus data: ' . $e->getMessage();
            $status = 'error';
        }
    } else {
        // Menampilkan pesan jika ID tidak valid
        $message = 'ID tidak valid.';
        $status = 'error';
    }
} else {
    // Menampilkan pesan jika tidak ada ID yang diberikan
    $message = 'Tidak ada ID yang diberikan.';
    $status = 'error';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hapus Data</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<script>
    // Menampilkan SweetAlert2 berdasarkan hasil operasi
    Swal.fire({
        title: "<?php echo ucfirst($status); ?>!",
        text: "<?php echo $message; ?>",
        icon: "<?php echo $status; ?>",
        confirmButtonText: 'OK'
    }).then(function() {
        // Redirect ke halaman tampil_data.php setelah 2 detik
        window.location.href = "isi_biodata.php";
    });
</script>

</body>
</html>
