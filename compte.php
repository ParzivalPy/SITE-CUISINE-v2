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
            <div class="info-bloc profil">
                <div class="photo" style="background-image: url('https://www.simplyrecipes.com/thmb/4rVYqq80fd-kHTx25yKtd8bvHzA=/1500x0/filters:no_upscale():max_bytes(150000):strip_icc()/Simply-Pasta-Carbonara-LEAD-4-3c433b3057e7465b8738b43de762df06.jpg');">
                    <div class="add_photo"><div class="add_photo_sub"><span class="material-symbols-outlined">add_photo_alternate</span></div></div>
                </div>
                <div class="infos">
                    <div class="pseudo">
                        <div class="actual">John Doe</div>
                        <div class="modify"><span class="material-symbols-outlined">edit</span></div>
                    </div>
                    <div class="min_infos">
                        <div class="time_membership">Membre depuis 01/01/2000</div>
                        <div class="point"></div>
                        <div class="number_recipes">18 recettes</div>
                        <div class="point"></div>
                        <div class="number_likes">34 likes</div>
                    </div>
                </div>
                <div class="logout">
                    <span class="material-symbols-outlined" width="24">logout</span>
                    <div class="logout-text">Se déconnecter</div>
                </div>
            </div>

            <div class="info-bloc private-info">
                <div class="zone-name">
                    <span class="material-symbols-outlined" width="36">logout</span>
                    <div class="my-private-info">Mes informations privées</div>
                    <div class="not-public-text">Ces informations ne sont pas publiquement accessibles</div>
                </div>
                <div class="ruler"></div>
                <div class="info-item">
                    <div class="header">Adresse mail</div>
                    <div class="info-box">
                        <span class="material-symbols-outlined">emergency_home</span>
                        <input type="text" value="">
                    </div>
                </div>
                <div class="personnal-info">
                    <div class="nom">
                        <div class="header">prénom</div>
                        <div class="info-box">
                            <input type="text" value="">
                        </div>
                    </div>
                    <div class="nom">
                    <div class="header">nom</div>
                        <div class="info-box">
                            <input type="text" value="">
                        </div>
                    </div>
                </div>
                <div class="save-info">
                    <span class="material-symbols-outlined">check</span>
                    <div>Sauvegarder les changements</div>
                </div>
            </div>
            
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
                    <div class="delete-button">
                        <span class="material-symbols-outlined">delete</span>
                        <div>Supprimer mon compte</div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
<?php
    include_once("includes/footer.php");
    ?>
</body>
</html>