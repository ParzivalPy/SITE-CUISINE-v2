<?php
error_reporting(E_ALL);
ini_set('display_errors',1);
?>

<!DOCTYPE html>
<html lang="fr-FR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gab'recettes</title>
    <link rel="stylesheet" href="assets\css\compte.css">
    <link rel="stylesheet" href="assets\css\nav-foo.css">
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
        <div class="center-container">
            <div class="info-bloc private-info">
                <div class="zone-name">
                    <span class="material-symbols-outlined" width="36">login</span>
                    <div class="my-private-info">Me connecter</div>
                    <div class="not-public-text">Si aucun compte n'est trouvé, vous pourrez en créer un</div>
                </div>
                <div class="ruler"></div>
                <div class="info-item">
                    <div class="header">Adresse mail</div>
                    <div class="info-box">
                        <span class="material-symbols-outlined">mail</span>
                        <input type="text" value="">
                    </div>
                </div>
                <div class="info-item">
                    <div class="header">Mot de passe</div>
                    <div class="info-box">
                        <span class="material-symbols-outlined">password</span>
                        <input type="text" value="">
                    </div>
                </div>
                <div class="save-info">
                    <span class="material-symbols-outlined">check</span>
                    <div>Se connecter</div>
                </div>
            </div>
        </div>
    </div>
    <?php
    include_once("includes/footer.php");
    ?>
</body>
</html>