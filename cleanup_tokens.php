<?php
require_once 'koneksi.php';

function verifyJWT($jwt, $secretKey) {
    $parts = explode('.', $jwt);
    if (count($parts) !== 3) return false;
    [$base64Header, $base64Payload, $base64Signature] = $parts;

    $signature = hash_hmac('sha256', "$base64Header.$base64Payload", $secretKey, true);
    $expectedSignature = base64UrlEncode($signature);
    if (!hash_equals($expectedSignature, $base64Signature)) return false;

    $payload = json_decode(base64UrlDecode($base64Payload), true);
    return (isset($payload['exp']) && time() <= $payload['exp']) ? $payload : false;
}

$secretKey = 'O4rl2PRwHX';

$sql = "SELECT user_id, jwt_token FROM users WHERE jwt_token IS NOT NULL";
$stmt = $pdo->query($sql);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($users as $user) {
    $payload = verifyJWT($user['jwt_token'], $secretKey);
    if ($payload === false) {
        $deleteSql = "UPDATE users SET jwt_token = NULL WHERE user_id = ?";
        $deleteStmt = $pdo->prepare($deleteSql);
        $deleteStmt->execute([$user['user_id']]);
    }
}
?>
