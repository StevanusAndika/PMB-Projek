<?php 
session_start();
// Generate CSRF Token
$csrfToken = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrfToken;
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>Sign Up</title>
    <link href="assets/css/tabler.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- FontAwesome -->
</head>

<body class="d-flex flex-column">
    <div class="page page-center">
        <div class="container container-tight py-4">
            <form id="registration-form" method="POST" onsubmit="handleSubmit(event)">
                <div class="card-body">
                    <h2 class="card-title text-center mb-4">Buat Akun Baru</h2>
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" placeholder="Enter name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" placeholder="Enter email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <div class="input-group">
                            <input type="password" name="password" class="form-control" placeholder="Password" required id="password">
                            <span class="input-group-text" id="eye-icon" style="cursor: pointer;">
                                <i class="fa fa-eye" id="eye-icon"></i>
                            </span>
                        </div>
                    </div>
                    <div class="form-footer">
                        <button type="submit" class="btn btn-primary w-100">Buat Akun Baru</button>
                    </div>
                </div>
                
                <div class="mb-3 text-center mt-3">
                        <a href="index.php">Login</a> | <a href="reset_password.php">Lupa Password?</a> | <a href="http://localhost/PMB-Projek/assets/brosur/Pengisian%20Formulir.pdfz">Liat Panduan Pendaftaran</a>
                    </div>

            </form>
        </div>
    </div>

    <script>
        // Toggle password visibility
        const eyeIcon = document.getElementById('eye-icon');
        const passwordInput = document.getElementById('password');

        eyeIcon.addEventListener('click', () => {
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        });

        async function handleSubmit(event) {
            event.preventDefault();

            const form = document.getElementById('registration-form');
            const formData = new FormData(form);

            try {
                const response = await fetch('register_process.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.text(); // We expect HTML output (SweetAlert) from the server

                // If the response contains "window.location.href", redirect the user
                if (result.includes('window.location.href')) {
                    // Show success popup and then redirect to login page
                    Swal.fire({
                        title: 'Success',
                        text: 'Anda berhasil registrasi, mengarahkan ke halaman login secara otomatis.',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.href = "index.php"; // Redirect to login page
                    });
                } else {
                    // If there's an error, show it using SweetAlert
                    Swal.fire({
                        title: 'Error',
                        text: 'Terjadi kesalahan. Silakan coba lagi.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            } catch (error) {
                Swal.fire({
                    title: 'Error',
                    text: 'Terjadi kesalahan. Silakan coba lagi.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        }
    </script>
</body>

</html>
