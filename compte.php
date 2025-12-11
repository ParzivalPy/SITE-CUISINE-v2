<?php
error_reporting(E_ALL);
ini_set('display_errors',1);
session_start();

$_SESSION['page'] = 'compte.php';

include_once("includes/db.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    session_destroy();
    header("Location: compte.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_info'])) {
    if ($_POST['first_name'] !== $_SESSION['LOGGED_USER']['first_name']) {
        update_user_info('first_name', $_POST['first_name'] ?? '', $host, $username, $password);
        $_SESSION['LOGGED_USER']['first_name'] = $_POST['first_name'] ?? '';
    }
    if ($_POST['last_name'] !== $_SESSION['LOGGED_USER']['last_name']) {
        update_user_info('last_name', $_POST['last_name'] ?? '', $host, $username, $password);
        $_SESSION['LOGGED_USER']['last_name'] = $_POST['last_name'] ?? '';
    }
    if ($_POST['email'] !== $_SESSION['LOGGED_USER']['email']) {
        update_user_info('email', $_POST['email'] ?? '', $host, $username, $password);
        $_SESSION['LOGGED_USER']['email'] = $_POST['email'] ?? '';
    }
    echo "<script>alert('Information updated successfully');</script>";
    header("Location: compte.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pseudo'])) {
    if ($_POST['first_name'] !== $_SESSION['LOGGED_USER']['first_name']) {
        update_user_info('pseudo', $_POST['pseudo'] ?? '', $host, $username, $password);
        $_SESSION['LOGGED_USER']['pseudo'] = $_POST['pseudo'] ?? '';
    }
    header("Location: compte.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0) {
    $uploadDir = 'assets/img';
    $filename = basename($_FILES['profile_picture']['name']);
    $extension = pathinfo($filename, PATHINFO_EXTENSION);
    $targetFilePath = $uploadDir . '/' . $_SESSION['LOGGED_USER']['id'] . '.' . $extension;
    if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFilePath)) {
        $conn = connect_to_database($host, $username, $password, "cuisine_base");
        $userId = intval($_SESSION['LOGGED_USER']['id']);
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

    echo "<script>alert('Votre compte va être supprimé définitivement');</script>";

    $conn = connect_to_database($host, $username, $password, "cuisine_base");
    $userId = intval($_SESSION['LOGGED_USER']['id']);

    request_database($conn, "DELETE FROM recettes WHERE id_author = $userId");
    request_database($conn, "DELETE FROM likes WHERE id_author = $userId");
    request_database($conn, "DELETE FROM profils WHERE id = $userId");

    $conn->close();

    session_destroy();
    header("Location: index.php");
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
        <?php if(!isset($_SESSION['LOGGED_USER']['email'])): ?>
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
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $mail = isset($_POST['mail']) ? $_POST['mail'] : '';
            $user_password = isset($_POST['password']) ? $_POST['password'] : '';
            $hashed_password = password_hash($user_password, PASSWORD_DEFAULT);
            
            $conn = connect_to_database($host, $username, $password, "cuisine_base");

            $profile= request_database($conn, "SELECT * FROM profils WHERE email = '" . $conn->real_escape_string($mail) . "'")->fetch_assoc();
            global $num_recipes;
            global $num_likes;
            $num_recipes = request_database($conn, "SELECT COUNT(*) AS count FROM recettes WHERE id_author = " . intval($profile['id']))->fetch_assoc()['count'];
            $num_likes = request_database($conn, "SELECT COUNT(*) AS count FROM likes WHERE id_author = " . intval($profile['id']))->fetch_assoc()['count'];
            $conn->close();
            
            if ($profile && password_verify($user_password, $profile['password'])) {
                $_SESSION['LOGGED_USER']['email'] = $profile['email'];
                $_SESSION['LOGGED_USER']['id'] = $profile['id'];
                $_SESSION['LOGGED_USER']['first_name'] = $profile['first_name'];
                $_SESSION['LOGGED_USER']['last_name'] = $profile['last_name'];
                $_SESSION['LOGGED_USER']['pseudo'] = $profile['pseudo'];

                header("Location: compte.php");
            } else {
                echo "Échec de la connexion : email ou mot de passe incorrect.";
            }
        }
        ?>




        <?php else: 
        $conn = connect_to_database($host, $username, $password, "cuisine_base");
        $num_recipes = request_database($conn, "SELECT COUNT(*) AS count FROM recettes WHERE id_author = " . intval($_SESSION['LOGGED_USER']['id']))->fetch_assoc()['count'];
        $num_likes = request_database($conn, "SELECT COUNT(*) AS count FROM likes WHERE id_author = " . intval($_SESSION['LOGGED_USER']['id']))->fetch_assoc()['count'];
        $result = request_database($conn, "SELECT beginning FROM profils WHERE id = " . intval($_SESSION['LOGGED_USER']['id']))->fetch_assoc();
        $beginning = $result['beginning'] ?? date('Y-m-d');
        $conn->close();
        ?>
        <div class="center-container">
            <div class="info-bloc profil">
                <div class="photo" style="background-position: center; background-image: url('assets/img/<?php echo htmlspecialchars($_SESSION['LOGGED_USER']['id']); ?>.jpeg');">
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
                        <div class="actual"><?php echo htmlspecialchars($_SESSION['LOGGED_USER']['pseudo']); ?></div>
                        <form method="POST" style="display:block;">
                            <input type="text" autocomplete="off" name="pseudo" class="pseudo-input actual" style="display:none; background-color: transparent; outline: none; border: none; border-bottom: 2px solid black; border-radius: 3px" value="<?php echo htmlspecialchars($_SESSION['LOGGED_USER']['pseudo']); ?>">
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
                        <div class="time_membership">Membre depuis <?php echo date('d/m/Y', strtotime($beginning)); ?></div>
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
                        <?php if (!isset($_SESSION['LOGGED_USER']['email'])): ?>
                        <span class="material-symbols-outlined" style="color: #FF0000">emergency_home</span>
                        <?php endif; ?>
                        <input autocomplete="off" name="email" type="text" value="<?php echo htmlspecialchars($_SESSION['LOGGED_USER']['email']); ?>">
                    </div>
                </div>
                <div class="personnal-info">
                    <div class="nom">
                        <div class="header">prénom</div>
                        <div class="info-box">
                            <input autocomplete="off" name="first_name" type="text" value="<?php echo htmlspecialchars($_SESSION['LOGGED_USER']['first_name']); ?>">
                        </div>
                    </div>
                    <div class="nom">
                    <div class="header">nom</div>
                        <div class="info-box">
                            <input autocomplete="off" name="last_name" type="text" value="<?php echo htmlspecialchars($_SESSION['LOGGED_USER']['last_name']); ?>">
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
                    <form method="POST">
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