<?php
declare(strict_types=1);


header('Content-Type: application/json');


// Inclusion du middleware JWT
$user = require_once __DIR__ . '/../auth/middleware.php';


// À ce stade, l'utilisateur est authentifié


echo json_encode([
'success' => true,
'user' => [
'id' => $user->user_id,
'email' => $user->email
]
]);

function getUserMail() {
    global $user;
    return $user->email;
}

function getUserId() {
    global $user;
    return $user->user_id;
}
?>