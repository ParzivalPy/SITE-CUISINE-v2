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
$stmt = $pdo->prepare('SELECT id, password FROM profils WHERE email = :email');
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
    'exp' => time() + 3600,
];

$secret = $_ENV['JWT_SECRET'] ?? null;

if (!$secret) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'JWT secret not configured.']);
    exit;
}

$jwt = JWT::encode($payload, $secret, 'HS256');

echo json_encode(['success' => true, 'token' => $jwt]);

?>