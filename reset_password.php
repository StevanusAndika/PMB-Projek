<?php 
session_start();
// Generate CSRF Token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier']); // Bisa username atau email
    $newPassword = trim($_POST['new_password']);
    $confirmPassword = trim($_POST['confirm_password']);

    // Validate CSRF Token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $message = "Invalid CSRF token.";
        $status = "error";
    } else {
        unset($_SESSION['csrf_token']); // Remove the CSRF token after validation

        // Validasi input
        if (empty($identifier) || empty($newPassword) || empty($confirmPassword)) {
            $message = "Semua field wajib diisi!";
            $status = "error";
        } elseif ($newPassword !== $confirmPassword) {
            $message = "Password baru dan konfirmasi password tidak cocok!";
            $status = "error";
        } else {
            require_once 'koneksi.php';

            try {
                // Cek apakah identifier adalah email atau username
                $query = "SELECT * FROM users WHERE username = :identifier OR email = :identifier";
                $stmt = $pdo->prepare($query);
                $stmt->execute(['identifier' => $identifier]);

                if ($stmt->rowCount() > 0) {
                    // User ditemukan, update password
                    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
                    $updateQuery = "UPDATE users SET password = :password WHERE username = :identifier OR email = :identifier";
                    $updateStmt = $pdo->prepare($updateQuery);
                    $updateStmt->execute(['password' => $hashedPassword, 'identifier' => $identifier]);

                    if ($updateStmt->rowCount() > 0) {
                        $message = "Password berhasil diperbarui!";
                        $status = "success";
                    } else {
                        $message = "Tidak ada perubahan pada password.";
                        $status = "info";
                    }
                } else {
                    $message = "Username atau email tidak ditemukan.";
                    $status = "error";
                }
            } catch (PDOException $e) {
                $message = "Terjadi kesalahan: " . $e->getMessage();
                $status = "error";
            }
        }
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <title>Reset Password</title>
    <link href="assets/css/tabler.min.css" rel="stylesheet"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .password-toggle {
            cursor: pointer;
        }
    </style>
</head>
<body class="d-flex flex-column">
    <div class="page page-center">
        <div class="container container-tight py-4">
            <form class="card card-md" id="reset-password-form" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <div class="card-body">
                    <h2 class="card-title text-center mb-4">Reset Password</h2>
                    
                    <div class="mb-3">
                        <label class="form-label">Username atau Email</label>
                        <input type="text" name="identifier" id="identifier" class="form-control" placeholder="Masukkan username atau email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password Baru</label>
                        <div class="input-group input-group-flat">
                            <input type="password" name="new_password" id="new_password" class="form-control" placeholder="Masukkan password baru" required>
                            <span class="input-group-text password-toggle">
                                <i class="fa fa-eye" id="eye-icon-new"></i>
                            </span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Konfirmasi Password Baru</label>
                        <div class="input-group input-group-flat">
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Konfirmasi password baru" required>
                            <span class="input-group-text password-toggle">
                                <i class="fa fa-eye" id="eye-icon-confirm"></i>
                            </span>
                        </div>
                    </div>
                    <div class="form-footer">
                        <button type="submit" class="btn btn-primary w-100">Reset Password</button>
                    </div>
                    <div class="mt-4 text-center">
                        <a href="index.php">Login</a> | <a href="register.php">Registrasi</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php if (isset($message)): ?>
    <script>
        Swal.fire({
            icon: "<?php echo $status; ?>",
            title: "<?php echo ucfirst($status); ?>",
            text: "<?php echo $message; ?>",
            confirmButtonText: 'OK'
        }).then(() => {
            <?php if ($status === 'success'): ?>
                window.location.href = 'index.php';
            <?php endif; ?>
        });
    </script>
    <?php endif; ?>

    <script>
        // Toggle visibility for new password
        document.getElementById('eye-icon-new').addEventListener('click', function () {
            const passwordInput = document.getElementById('new_password');
            const eyeIcon = this;
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });

        // Toggle visibility for confirm password
        document.getElementById('eye-icon-confirm').addEventListener('click', function () {
            const passwordInput = document.getElementById('confirm_password');
            const eyeIcon = this;
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    </script>
</body>
</html>
