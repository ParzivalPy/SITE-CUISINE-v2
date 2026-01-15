<?php

declare(strict_types=1);

header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['email'], $input['password'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Email and password are required.']);
    exit;
}

$email = trim($input['email']);
$password = $input['password'];

$pdo = getDatabaseConnection();
$stmt = $pdo->prepare('SELECT * FROM profils WHERE email = :email');
$stmt->execute(['email' => $email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Invalid email or password.']);
    exit;
}

$payload = [
    'user_id' => $user['id'],
    'email' => $email,
    'first_name' => $user['first_name'],
    'last_name' => $user['last_name'],
    'pseudo' => $user['pseudo'],
    'beginning' => $user['beginning'],
    'iat' => time(),
    'nbf' => time(),
    'exp' => time() + 3600,
    'sub' => (string) $user['id'],
];

$secret = $_ENV['JWT_SECRET'] ?? null;

if (!$secret) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'JWT secret not configured.']);
    exit;
}

$jwt = JWT::encode($payload, $secret, 'HS256');

$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);

setcookie('token', $jwt, [
    'expires' => time() + 3600,
    'path' => '/',
    'httponly' => true,
    'samesite' => 'Lax',
    'secure' => $secure,
]);

echo json_encode(['success' => true, 'token' => $jwt]);

?>