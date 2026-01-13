<?php

declare(strict_types=1);

header('Content-Type: application/json');

require_once __DIR__ . '/../../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

$secret = $_ENV['JWT_SECRET'] ?? null;
if (!$secret) {
    http_response_code(500);
    echo json_encode(['error' => 'JWT secret not configured.']);
    exit;
}

$headers = getallheaders();

if (!isset($headers['Authorization'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Authorization header missing.']);
    exit;
}

if (!preg_match('/Bearer\s+(.*)$/', $headers['Authorization'], $matches)) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid Authorization header format.']);
    exit;
}

$token = $matches[1];

try {
    $decoded = JWT::decode($token, new Key($secret, 'HS256'));
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid token.']);
    exit;
}

return $decoded;
?>