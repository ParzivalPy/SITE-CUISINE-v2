<?php

$url_middleware = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/SITE-CUISINE-v2/www/api/auth/middleware.php';
$url_login = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/SITE-CUISINE-v2/www/api/auth/login.php';

function toaster($message, $type = 'info') {
    $jsMessage = json_encode($message);
    echo "<script>
        (function(){
            const msg = " . $jsMessage . ";
            alert(msg);
        })();
    </script>";
}

function verify_token(): array 
{
    $haveToConnect = true;
    $user = null;

    if (isset($_SERVER['HTTP_AUTHORIZATION']) || isset($_COOKIE['token'])) {
        global $url_middleware;
        $url = $url_middleware;
        // TODO : Update the URL according to the environment
        //* dev : /SITE-CUISINE-v2/www/api/auth/middleware.php
        //* prod : /api/auth/middleware.php
        $ch = curl_init($url);
        $token = $_SERVER['HTTP_AUTHORIZATION'] ?? (isset($_COOKIE['token']) ? 'Bearer ' . $_COOKIE['token'] : '');
        $headers = [
            'Accept: application/json',
            'Content-Type: application/json',
        ];
        if ($token !== '') {
            $headers[] = 'Authorization: ' . $token;
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 10,
        ]);

        $responseRaw = curl_exec($ch);
        $curlErr = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($responseRaw === false) {
            error_log('Error cURL while verifying token: ' . $curlErr);
            $haveToConnect = true;
        } else {
            $response = json_decode($responseRaw, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log('Invalid JSON response from middleware (HTTP ' . intval($httpCode) . ') : ' . $responseRaw);
                $haveToConnect = true;
            } else {
                if (empty($response['user_id'])) {
                    setcookie('token', '', time() - 3600, '/', '', false, true);
                    $user = null;
                } else {
                    $userId = (int)$response['user_id'];
                    try {
                        $db = getDatabaseConnection();
                        $stmt = $db->prepare('SELECT * FROM profils WHERE id = :id');
                        $stmt->execute(['id' => $userId]);
                        $user = $stmt->fetch(PDO::FETCH_ASSOC);
                        if (!$user) {
                            error_log('No profile found for id: ' . $userId);
                            $user = null;
                        }
                    } catch (Exception $e) {
                        echo 'Database error: ' . htmlspecialchars($e->getMessage());
                        echo '<script>alert("Database error: ' . htmlspecialchars($e->getMessage()) . '");</script>';
                        exit();
                    }
                }
            }

            $haveToConnect = false;
        }
    }

    return ['haveToConnect' => $haveToConnect, 'user' => $user];
}

function connect_to_account()
{
    $payload = ['email' => $_POST['mail'], 'password' => $_POST['password']];
    $json = json_encode($payload);

    global $url_login;
    $url = $url_login;

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json'],
        CURLOPT_POSTFIELDS => $json,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 10,
    ]);
    $response = curl_exec($ch);
    $curlErr = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false) {
        error_log("Login cURL error: $curlErr");
        echo "<div style=\"max-width: 600px\" class=\"error-message\"><span class=\"material-symbols-outlined\">error</span><span>Auth Server Connection Error</span></div>";
        
        exit();
    }

    $decoded = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Login returned invalid JSON (HTTP $httpCode): $response");
        echo "<div style=\"max-width: 600px\" class=\"error-message\"><span class=\"material-symbols-outlined\">error</span><span>Invalid response from Auth Server.</span></div>";
        exit();
    }

    if (isset($decoded['error'])) {
        echo "<div style=\"max-width: 600px\" class=\"error-message\"><span class=\"material-symbols-outlined\">error</span><span>Error: " . htmlspecialchars($decoded['error']) . "</span></div>";
    }

    if (!empty($decoded['token'])) {
        setcookie('token', $decoded['token'], time() + 3600, '/', '', false, true);
        header("Location: compte.php");
        exit();
    }
}
?>