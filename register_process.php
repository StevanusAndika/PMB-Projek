<?php
session_start();
require_once 'koneksi.php';

function base64UrlEncode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64UrlDecode($data) {
    return base64_decode(strtr($data, '-_', '+/'));
}

function generateJWT($payload, $secretKey) {
    $header = json_encode(['alg' => 'HS256', 'typ' => 'JWT']);
    $base64Header = base64UrlEncode($header);
    $base64Payload = base64UrlEncode(json_encode($payload));
    $signature = hash_hmac('sha256', "$base64Header.$base64Payload", $secretKey, true);
    $base64Signature = base64UrlEncode($signature);
    return "$base64Header.$base64Payload.$base64Signature";
}

// Verify CSRF Token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}
unset($_SESSION['csrf_token']); // Hapus token setelah diverifikasi

// Get Form Data
$name = trim($_POST['name']);
$email = trim($_POST['email']);
$password = trim($_POST['password']);
$role = 'pendaftar'; // Default role

// Validate Email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

// Check if Email Exists
$sql = "SELECT * FROM users WHERE email = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$email]);
if ($stmt->rowCount() > 0) {
    echo json_encode(['success' => false, 'message' => 'Email already registered']);
    exit;
}

// Hash Password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Generate JWT Token
$secretKey = '7uyEbKsaXI';
$issuedAt = time();
$expirationTime = $issuedAt + 300; // 5 minutes
$payload = [
    'iat' => $issuedAt,
    'exp' => $expirationTime,
    'email' => $email
];
$jwt = generateJWT($payload, $secretKey);

// Insert User into Database
$sql = "INSERT INTO users (username, email, password, role, jwt_token) VALUES (?, ?, ?, ?, ?)";
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$name, $email, $hashedPassword, $role, $jwt]);

    // Redirect with SweetAlert after successful registration
    echo '
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        Swal.fire({
            title: "Success",
            text: "Anda berhasil registrasi, mengarahkan ke halaman login secara otomatis.",
            icon: "success",
            confirmButtonText: "OK"
        }).then(function() {
            window.location.href = "index.php"; // Redirect to login page, adjust URL as necessary
        });
    </script>';
} catch (PDOException $e) {
    // Handle error and return failure message
    echo '
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        Swal.fire({
            title: "Error",
            text: "Registrasi gagal: ' . $e->getMessage() . '",
            icon: "error",
            confirmButtonText: "OK"
        });
    </script>';
}
?>
