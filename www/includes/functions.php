<?php

use Dom\Document;

$url_middleware = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/SITE-CUISINE-v2/www/api/auth/middleware.php';
$url_login = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/SITE-CUISINE-v2/www/api/auth/login.php';
// TODO : Update the URL according to the environment
//* dev : /SITE-CUISINE-v2/www/api/auth/middleware.php
//* prod : /api/auth/middleware.php

function toaster($title, $message, $type = 'info', $time = 5) {
    $jsMessage = json_encode($message);
    echo "
    <script>
    (function () {
        const run = () => {
            const containerId = 'toaster-list';
            let list = document.getElementById(containerId);
            if (!list) {
                list = document.createElement('div');
                list.id = containerId;
                list.className = 'toaster-list';
                document.body.appendChild(list);
            }

            const box = document.createElement('div');
            box.className = 'toaster-box " . $type . " show';
            box.innerHTML = \"<h4>" . htmlspecialchars($title, ENT_QUOTES) . "</h4><p>" . htmlspecialchars($message, ENT_QUOTES) . "</p>\";
            list.appendChild(box);

            setTimeout(() => {
                box.classList.remove('show');
                box.classList.add('hide');
                setTimeout(() => {
                    const siblings = Array.from(list.children).filter(el => el !== box);
                    const first = new Map();
                    siblings.forEach(el => first.set(el, el.getBoundingClientRect()));
                    if (box.parentNode === list) list.removeChild(box);
                    const last = new Map();
                    siblings.forEach(el => last.set(el, el.getBoundingClientRect()));
                    siblings.forEach(el => {
                        const f = first.get(el);
                        const l = last.get(el);
                        const deltaY = f.top - l.top;
                        if (deltaY) {
                            el.style.transform = `translateY(deltaY px)`;
                            el.getBoundingClientRect(); // force reflow
                            el.style.transform = '';
                        }
                    });
                }, 400);
            }," . ($time * 1000) . ");
        };

        if (document.body) run();
        else window.addEventListener('DOMContentLoaded', run, { once: true });
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
                        $stmt = $db->prepare('SELECT id, last_name, first_name, pseudo, beginning, email FROM profils WHERE id = :id');
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