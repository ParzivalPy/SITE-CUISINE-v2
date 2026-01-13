<?php

declare(strict_types=1);

header('Content-Type: application/json');

require_once '../config/database.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['last_name']) || !isset($input['first_name']) || !isset($input['pseudo']) || !isset($input['beginning']) || !isset($input['email']) || !isset($input['password'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit;
}

$last_name = trim($input['last_name']);
$first_name = trim($input['first_name']);
$pseudo = trim($input['pseudo']);
$beginning = trim($input['beginning']);
if (is_numeric($beginning)) {
    $ts = (int) $beginning;
    // if timestamp is in milliseconds convert to seconds
    if (strlen($beginning) > 10) {
        $ts = (int) floor($ts / 1000);
    }
    $beginning = date('Y-m-d H:i:s', $ts);
} else {
    try {
        $dt = new DateTime($beginning);
        $beginning = $dt->format('Y-m-d H:i:s');
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid beginning date format.']);
        exit;
    }
}
$email = trim($input['email']);
$password = $input['password'];

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email format.']);
    exit;
}

if (strlen($password) < 8) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters long.']);
    exit;
}

// TODO: pk ca marche pas ?

$pdo = getDatabaseConnection();

$stmt = $pdo->prepare('SELECT id FROM profils WHERE email = :email');
$stmt->execute(['email' => $email]);

if ($stmt->fetch()) {
    http_response_code(409);
    echo json_encode(['success' => false, 'message' => 'Email is already registered.']);
    exit;
}

$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

$stmt = $pdo->prepare('INSERT INTO profils (last_name, first_name, pseudo, beginning, email, password) VALUES (:last_name, :first_name, :pseudo, :beginning, :email, :password)');
$stmt->execute([
    'last_name' => $last_name,
    'first_name' => $first_name,
    'pseudo' => $pseudo,
    'beginning' => $beginning,
    'email' => $email,
    'password' => $hashedPassword
]);

http_response_code(201);
echo json_encode(['success' => true, 'message' => 'User registered successfully.']);
?>