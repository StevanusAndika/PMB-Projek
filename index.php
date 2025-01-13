<?php
session_start();
require_once 'koneksi.php';

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Proses login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validasi CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo json_encode(["status" => "error", "message" => "Invalid CSRF token"]);
        exit;
    }

    // Hapus CSRF token setelah digunakan
    unset($_SESSION['csrf_token']);

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Validasi input
    if (empty($email) || empty($password)) {
        echo json_encode(["status" => "error", "message" => "Email dan password wajib diisi."]);
        exit;
    }

    // Cek apakah email terdaftar
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // Cek role dan arahkan ke halaman sesuai
        $_SESSION['user'] = $user;
        if ($user['role'] === 'admin') {
            echo json_encode(["status" => "success", "redirect" => "dashboard/admin.php"]);
        } elseif ($user['role'] === 'pendaftar') {
            echo json_encode(["status" => "success", "redirect" => "dashboard/user.php"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Role tidak valid."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Email atau password salah."]);
    }
    exit;
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge"/>
    <title>Sistem Informasi Penerimaan Mahasiswa Baru | Universitas IPWIJA</title>
    <link href="assets/css/tabler.min.css" rel="stylesheet"/>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <style>
        .password-toggle {
            cursor: pointer;
        }
    </style>
</head>
<body class="d-flex flex-column">
    
    <div class="page page-center">
        <div class="container container-tight py-4">
            <h2 class="card-title text-center mb-4">Penerimaan Mahasiswa Baru Universitas IPWIJA</h2>

            <form class="card card-md" id="login-form" method="POST">
                <div class="card-body">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" id="email" class="form-control" placeholder="Enter email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <div class="input-group input-group-flat">
                            <input type="password" name="password" id="password" class="form-control" placeholder="Enter password" required>
                            <span class="input-group-text password-toggle" id="toggle-password">
                                <i class="fa fa-eye" id="eye-icon"></i>
                            </span>
                        </div>
                    </div>
                    
                    <div class="form-footer">
                        <button type="submit" class="btn btn-primary w-100">Login</button>
                    </div>

                    <div class="mb-3 text-center mt-3">
                        <a href="reset_password.php">Lupa password?</a> | <a href="register.php">Registrasi</a>
                    </div>

                     <!-- Link ke file PDF -->
                     <div class="mb-3 text-center">
                        <a href="assets/brosur/Pengisian%20Formulir.pdf" target="_blank" class="btn btn-link">
                            Lihat Cara Pendaftaran
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('toggle-password').addEventListener('click', function () {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });

        document.getElementById('login-form').addEventListener('submit', async function (e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);

    const response = await fetch(form.action, {
        method: 'POST',
        body: formData
    });

    const result = await response.json();

    if (result.status === 'success') {
        window.location.href = result.redirect;
    } else {
        if (result.message === 'Invalid CSRF token') {
            Swal.fire({
                title: 'Session Expired',
                text: 'Your session has expired. The page will refresh automatically.',
                icon: 'warning',
                timer: 3000,
                showConfirmButton: false,
                willClose: () => {
                    window.location.reload();
                }
            });
        } else {
            Swal.fire('Error', result.message, 'error');
        }
    }
});

    </script>
</body>
</html>
