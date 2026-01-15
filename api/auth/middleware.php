<?php

declare(strict_types=1);

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
    return null;
}

$token = null;

$headers = function_exists('getallheaders') ? getallheaders() : [];
$authHeader = null;

foreach ($headers as $k => $v) {
    if (strtolower($k) === 'authorization') {
        $authHeader = $v;
        break;
    }
}

if (!$authHeader && isset($_SERVER['HTTP_AUTHORIZATION'])) {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
}

if (!$authHeader && isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
    $authHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
}

if (!empty($_COOKIE['token'])) {
    $token = $_COOKIE['token'];
}

if (!$token && $authHeader) {
    if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
        $token = $matches[1];
    } else {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid Authorization header format']);
        return null;
    }
}

if (!$token) {
    http_response_code(401);
    return null;
}

try {
    $decoded = JWT::decode($token, new Key($secret, 'HS256'));
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode([
        'error' => 'Invalid or expired token',
        'message' => $e->getMessage()
    ]);
    return null;
}

$_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;

return $decoded;
