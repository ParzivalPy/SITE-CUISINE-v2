<?php

declare(strict_types=1);

header('Content-Type: application/json');

require_once __DIR__ . '/../../../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../../../');
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
    JWT::$leeway = 300; // allow 5 minutes clock skew
    $decoded = JWT::decode($token, new Key($secret, 'HS256'));
} catch (ExpiredException $e) {
    http_response_code(401);
    $payload = $e->getPayload();
    $exp = property_exists($payload, 'exp') ? (int) $payload->exp : null;
    $expire_at = $exp ? date('Y-m-d H:i:s', $exp) : null;
    echo json_encode([
        'error' => 'Expired token',
        'expire_at' => $expire_at,
        'expire_at_unix' => $exp,
        'treatment_date' => date('Y-m-d H:i:s', time()),
        'message' => $e->getMessage()
    ]);
    return null;
} catch (Exception $e) {
    http_response_code(401);
    $headerClaims = null;
    $payloadClaims = null;
    if (!empty($token)) {
        $parts = explode('.', $token);
        if (count($parts) === 3) {
            $h = $parts[0];
            $p = $parts[1];
            $base64url_decode = function ($data) {
                $remainder = strlen($data) % 4;
                if ($remainder) {
                    $data .= str_repeat('=', 4 - $remainder);
                }
                return base64_decode(strtr($data, '-_', '+/'));
            };
            $hdrJson = $base64url_decode($h);
            $plJson = $base64url_decode($p);
            $headerClaims = $hdrJson ? json_decode($hdrJson, true) : null;
            $payloadClaims = $plJson ? json_decode($plJson, true) : null;
        }
    }

    echo json_encode([
        'error' => 'Invalid or expired token',
        'treatment_date' => date('Y-m-d H:i:s', time()),
        'message' => $e->getMessage(),
        'token_header' => $headerClaims,
        'token_payload' => $payloadClaims
    ]);
    return null;
}

$_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;

echo json_encode($decoded);
