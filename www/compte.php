<?php
error_reporting(E_ALL);
ini_set('display_errors',1);

require_once("api/config/database.php");

session_start();

$user = null;
if (isset($_SERVER['HTTP_AUTHORIZATION']) || isset($_COOKIE['token'])) {
    $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/SITE-CUISINE-v2/www/api/auth/middleware.php';
    $ch = curl_init($url);
    $headers = [
        "Content-Type: application/json",
        "Accept: application/json",
        "Authorization: " . ($_SERVER['HTTP_AUTHORIZATION'] ?? 'Bearer ' . ($_COOKIE['token'] ?? '')),
    ];
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => $headers,
    ]);
    $response = curl_exec($ch);
    $curlErr = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    var_dump($response);
    print_r($headers);
    if ($response === false) {
        echo json_encode(['success' => false, 'error' => 'curl_error', 'message' => $curlErr]);
        exit();
    }
}

$haveToConnect = false;

if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
    $haveToConnect = true;
}

$_SESSION['page'] = 'compte.php';

include_once("includes/db.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    session_destroy();
    setcookie('token', '', time() - 3600, '/', '', false, true);
    header("Location: compte.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_info'])) {
    $pdo = getDatabaseConnection();
    if ($_POST['first_name'] !== $user['first_name']) {
        $update = $pdo->prepare("UPDATE profils SET first_name = :first_name WHERE id = :id")->execute(['first_name' => $_POST['first_name'] ?? '','id' => $user['id']]);
        $user['first_name'] = $_POST['first_name'] ?? '';
    }
    if ($_POST['last_name'] !== $user['last_name']) {
        $update = $pdo->prepare("UPDATE profils SET last_name = :last_name WHERE id = :id")->execute(['last_name' => $_POST['last_name'] ?? '','id' => $user['id']]);
        $user['last_name'] = $_POST['last_name'] ?? '';
    }
    if ($_POST['email'] !== $user['email']) {
        $update = $pdo->prepare("UPDATE profils SET email = :email WHERE id = :id")->execute(['email' => $_POST['email'] ?? '','id' => $user['id']]);
        $user['email'] = $_POST['email'] ?? '';
    }
    echo "<script>alert('Information updated successfully');</script>";
    header("Location: compte.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pseudo'])) {
    $pdo = getDatabaseConnection();
    if ($_POST['pseudo'] !== $user['pseudo']) {
        $update = $pdo->prepare("UPDATE profils SET pseudo = :pseudo WHERE id = :id")->execute(['pseudo' => $_POST['pseudo'] ?? '','id' => $user['id']]);
        $user['pseudo'] = $_POST['pseudo'] ?? '';
    }
    header("Location: compte.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0) {
    $uploadDir = 'assets/img';
    $filename = basename($_FILES['profile_picture']['name']);
    $extension = pathinfo($filename, PATHINFO_EXTENSION);
    $targetFilePath = $uploadDir . '/' . $user['id'] . '.' . $extension;
    if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFilePath)) {
        $conn = connect_to_database($host, $username, $password, "cuisine_base");
        $userId = intval($user["id"]);
        $escapedFilePath = $conn->real_escape_string($targetFilePath);
        $conn->close();
        echo "<script>alert('Image de profil mise à jour avec succès');</script>";
    } else {
        echo "<script>alert('Erreur lors du téléchargement de l\'image');</script>";
    }
    
    header("Location: compte.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_account'])) {
    $pdo = getDatabaseConnection();

    echo "<script>
    confirm('Votre compte va être supprimé définitivement');
    </script>";

    // TODO : rajouter une condition pour que quand on annule la suppression, ça ne supprime pas le compte

    $pdo->prepare("DELETE FROM likes WHERE id_author = :id")->execute(['id' => $user['id']]);
    $pdo->prepare("DELETE FROM recettes WHERE id_author = :id")->execute(['id' => $user['id']]);
    $pdo->prepare("DELETE FROM profils WHERE id = :id")->execute(['id' => $user['id']]);

    session_destroy();
    setcookie('token', '', time() - 3600, '/', '', false, true);
    header("Location: compte.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr-FR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gab'recettes</title>
    <link rel="stylesheet" href="assets/css/compte.css">
    <link rel="stylesheet" href="assets/css/nav-foo.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
</head>
<body>
    <?php
    include_once("includes/navbar.php");
    ?>
    <div class="body">
        <?php if($haveToConnect): ?>
        <div class="body">
            <div class="center-container">
                <form action="compte.php" method="post">
                    <div class="info-bloc private-info">
                        <div class="zone-name">
                            <span class="material-symbols-outlined" width="36">login</span>
                            <div class="my-private-info">Me connecter</div>
                            <div class="not-public-text">Si aucun compte n'est trouvé, vous pourrez en créer un</div>
                        </div>
                        <div class="ruler"></div>
                        <div class="info-item">
                            <label class="header" for="mail">Adresse mail</label>
                            <div class="info-box">
                                <span class="material-symbols-outlined">mail</span>
                                <input type="text" id="mail" name="mail" value="">
                            </div>
                        </div>
                        <div class="info-item">
                            <label class="header" for="password">Mot de passe</label>
                            <div class="info-box">
                                <span class="material-symbols-outlined">password</span>
                                <input type="password" id="password" name="password" value="">
                            </div>
                        </div>
                        <input type="submit"  class="save-info" style="border:none;cursor:pointer;" value="Se connecter">
                    </div>
                </form>
            </div>
        </div>
        <?php

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mail'], $_POST['password'])) {
            $_POST[] = null;

            $payload = ['email' => $_POST['mail'], 'password' => $_POST['password']];
            $jsonPayload = json_encode($payload);

            $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
                . '://' . $_SERVER['HTTP_HOST'] . '/SITE-CUISINE-v2/www/api/auth/login.php';
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json'],
                CURLOPT_POSTFIELDS => $jsonPayload,
            ]);
            $response = curl_exec($ch);
            $curlErr = curl_error($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);


            if ($response === false) {
                echo json_encode(['success' => false, 'error' => 'curl_error', 'message' => $curlErr]);
                exit();
            }

            $decoded = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                echo json_encode(['success' => false, 'error' => 'invalid_json_response', 'http_code' => $httpCode, 'raw' => $response]);
                exit();
            }

            // Optionally populate session on successful login if the login endpoint returns user/token
            if (!empty($decoded['success']) && $decoded['success'] == true && !empty($decoded['token'])) {
                if (!empty($decoded['token'])) {
                    setcookie('token', $decoded['token'], time() + 3600, '/', '', false, true);
                }
            }

            setcookie('token', $decoded['token'] ?? '', time() + 3600, '/', '', false, true);

            echo json_encode($decoded);

            header("Location: compte.php");
            exit();
        }

        ?>




        <?php else: 
        $conn = getDatabaseConnection();

        $num_recipes = $conn->query("SELECT COUNT(*) AS count FROM recettes WHERE id_author = " . $user["id"]);
        $num_recipes = $num_recipes->fetch()['count'];

        $num_likes = $conn->query("SELECT COUNT(*) AS count FROM likes WHERE id_author = " . $user["id"]);
        $num_likes = $num_likes->fetch()['count'];

        ?>
        <div class="center-container">
            <div class="info-bloc profil">
                <div class="photo" style="background-position: center; background-image: url('assets/img/<?php echo htmlspecialchars($user["id"]); ?>.jpeg');">
                    <div class="add_photo"><div class="add_photo_sub" id="btn-modify-img" style="cursor: pointer;"><span class="material-symbols-outlined">add_photo_alternate</span></div></div>
                    <div class="img-modify" id="photo_form">
                        <div class="img-title"><span class="material-symbols-outlined">edit</span> Changer la photo de profil</div>
                        <form action="compte.php" method="post" enctype="multipart/form-data">
                            <input type="file" name="profile_picture" accept="image/*" required>
                            <input type="submit" value="Valider l'image" name="submit-image" id="submit-image">
                            <script>
                                document.querySelector('input[name="profile_picture"]').addEventListener('change', function(e) {
                                    const file = e.target.files[0];
                                    if (file) {
                                        const reader = new FileReader();
                                        reader.onload = function(event) {
                                            document.getElementById('previewImg').src = event.target.result;
                                            document.getElementById('previewImg').style.display = 'block';
                                        };
                                        reader.readAsDataURL(file);
                                    }
                                });

                                document.getElementById('btn-modify-img').addEventListener('click', function() {
                                    const input = document.querySelector('.img-modify');
                                    
                                    if (input.style.display == 'flex') {
                                        input.style.display = 'none';
                                    } else {
                                        input.style.display = 'flex';
                                    }
                                });
                            </script>
                        </form>
                        <div class="img-preview">
                            <img id="previewImg" src="#" alt="Aperçu de l'image" style="display:none; max-width: 100%; height: auto; margin-top: 10px;">
                            <div class="filter"></div>
                        </div>
                    </div>
                </div>
                <div class="infos">
                    <div class="pseudo">
                        <div class="actual"><?php echo htmlspecialchars($user['pseudo']); ?></div>
                        <form method="POST" style="display:block;">
                            <input type="text" autocomplete="off" name="pseudo" class="pseudo-input actual" style="display:none; background-color: transparent; outline: none; border: none; border-bottom: 2px solid black; border-radius: 3px" value="<?php echo htmlspecialchars($user['pseudo']); ?>">
                        </form>
                        <div class="modify" style="cursor: pointer;"><span class="material-symbols-outlined">edit</span></div>
                        <script>
                            document.querySelector('.modify').addEventListener('click', function() {
                                const actual = document.querySelector('.pseudo .actual');
                                const input = document.querySelector('.pseudo-input');
                                
                                if (input.style.display === 'none') {
                                    actual.style.display = 'none';
                                    input.style.display = 'block';
                                    input.focus();
                                } else {
                                    actual.style.display = 'block';
                                    input.style.display = 'none';
                                }
                            });
                        </script>
                    </div>
                    <div class="min_infos">
                        <div class="time_membership">Membre depuis <?php echo date('d/m/Y', strtotime($user['beginning'])); ?></div>
                        <div class="point"></div>
                        <div class="number_recipes"><?php if ($num_recipes<2) {echo "$num_recipes recette";} else {echo "$num_recipes recettes";} ?> </div>
                        <div class="point"></div>
                        <div class="number_likes"><?php if ($num_likes<2) {echo "$num_likes like";} else {echo "$num_likes likes";} ?></div>
                    </div>
                </div>
                <form method="POST" style="display:inline;">
                    <button type="submit" name="logout" class="logout" style="border:none;cursor:pointer;">
                        <span class="material-symbols-outlined" width="24">logout</span>
                        <div class="logout-text">Se déconnecter</div>
                    </button>
                </form>
            </div>

            <form method="POST" class="info-bloc private-info">
                <div class="zone-name">
                    <span class="material-symbols-outlined" width="36">logout</span>
                    <div class="my-private-info">Mes informations privées</div>
                    <div class="not-public-text">Ces informations ne sont pas publiquement accessibles</div>
                </div>
                <div class="ruler"></div>
                <div class="info-item">
                    <div class="header">Adresse mail</div>
                    <div class="info-box">
                        <?php if (!isset($user['email'])): ?>
                        <span class="material-symbols-outlined" style="color: #FF0000">emergency_home</span>
                        <?php endif; ?>
                        <input autocomplete="off" name="email" type="text" value="<?php echo htmlspecialchars($user['email']); ?>">
                    </div>
                </div>
                <div class="personnal-info">
                    <div class="nom">
                        <div class="header">prénom</div>
                        <div class="info-box">
                            <input autocomplete="off" name="first_name" type="text" value="<?php echo htmlspecialchars($user['first_name']); ?>">
                        </div>
                    </div>
                    <div class="nom">
                    <div class="header">nom</div>
                        <div class="info-box">
                            <input autocomplete="off" name="last_name" type="text" value="<?php echo htmlspecialchars($user['last_name']); ?>">
                        </div>
                    </div>
                </div>
                <div method="POST" style="display:inline;">
                    <button name="update_info" type="submit" class="save-info" style="border:none;cursor:pointer;">
                        <span class="material-symbols-outlined">check</span>
                        <div>Sauvegarder les changements</div>
                    </button>
                </div>
            </form>
            
            <div class="info-bloc private-info">
                <div class="zone-name">
                    <span class="material-symbols-outlined" width="36">where_to_vote</span>
                    <div class="my-private-info">Mon accès au compte</div>
                    <div class="not-public-text">Ces informations ne sont pas publiquement accessibles</div>
                </div>
                <div class="ruler"></div>
                <div class="account-management">
                    <div class="header">Supprimer mon compte</div>
                    <div class="delete-text">Le bouton ci-dessous ouvre un formulaire vous permettant de choisir et de <span>confirmer</span> les détails de <span>suppression de votre compte</span>.</div>
                    <div class="delete-text">La suppression d'un compte est <span>définitive</span>.</div>
                    <form method="POST" id="deleteAccountForm" style="display:inline;">
                        <button name="delete_account" class="delete-button" style="border:none;cursor:pointer;">
                            <span class="material-symbols-outlined">delete</span>
                            <div>Supprimer mon compte</div>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>


    
<?php
    include_once("includes/footer.php");
    ?>
</body>
</html>