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
    <link rel="stylesheet" href="assets\css\style.css">
    <link rel="stylesheet" href="assets\css\nav-foo.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
</head>
<body>
    <?php
    include_once("includes/navbar.php");
    include_once("includes/db.php");

    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $conn = connect_to_database($host, $username, $password, "cuisine_base");
    $recipesResult = request_database($conn, "SELECT r.*, p.pseudo AS author_pseudo FROM recettes r LEFT JOIN profils p ON r.id_author = p.id WHERE r.title LIKE '%" . $conn->real_escape_string($search) . "%'");
    $recipes = $recipesResult->fetch_all(MYSQLI_ASSOC);
    ?>
    <div class="menu">
        <div class="header">
            <h1>Trouvez le plat <span>parfait</span></h1>
        </div>
        <div class="research-zone">
            <div class="research-bar">
                <span class="material-symbols-outlined">search</span>
                <form action="index.php" method="get">
                    <input type="text" placeholder="Chercher une recette" id="search" name="search">
                </form>
            </div>
            <div class="indications">
                Essayez: <span>'blanquette de veau'</span> ou <span>'tarte aux pommes'</span>
            </div>
        </div>
        <div class="filters">
            <div class="filter">
                <p>Type</p>
                <span class="material-symbols-outlined">keyboard_arrow_down</span>
            </div>
            <div class="filter">
                <p>Temps de préparation</p>
                <span class="material-symbols-outlined">keyboard_arrow_down</span>
            </div>
            <div class="filter">
                <p>Origine</p>
                <span class="material-symbols-outlined">keyboard_arrow_down</span>
            </div>
        </div>
    </div>
    <div class="recipe-grid">
        <?php foreach ($recipes as $recipe): ?>
        <div class="recipe">
            <div class="photo" style="background-image: url('https://www.simplyrecipes.com/thmb/4rVYqq80fd-kHTx25yKtd8bvHzA=/1500x0/filters:no_upscale():max_bytes(150000):strip_icc()/Simply-Pasta-Carbonara-LEAD-4-3c433b3057e7465b8738b43de762df06.jpg');"></div>
            <div class="informations">
                <div class="up">
                    <div class="titleandauthor">
                        <div class="title"><?php echo $recipe['title']; ?></div>
                        <div class="author">Recette par <span>
                        <?php 
                        echo $recipe['author_pseudo'] ?? 'Auteur inconnu';
                        ?></span></div>
                    </div>
                    <div class="time"><?php echo $recipe['prep_time'] + $recipe['baking_time']; ?>'</div>
                </div>
                <div class="down">
                    <div class="categories">
                        <div class="categorie"><span class="material-symbols-outlined">stockpot</span><p><?php echo $recipe['category']; ?></p></div>
                        <div class="categorie"><span class="material-symbols-outlined">sentiment_very_satisfied</span><p>Difficulté : 
                            <?php 
                            $diff = $recipe['difficulty']; 
                            switch ($diff) {
                                case 1:
                                    echo "Facile";
                                    break;
                                case 2:
                                    echo "Moyenne";
                                    break;
                                case 3:
                                    echo "Difficile";
                                    break;
                                default:
                                    echo "Inconnue";
                            }
                            ?></p></div>
                        <div class="categorie"><img src="https://kapowaz.github.io/square-flags/flags/<?php echo strtolower($recipe['origin']); ?>.svg" width="20" alt="?" style="border-radius: 3px; margin: 2px; display: flex; align-items: center; justify-content: center;"/><?php echo $pays[$recipe['origin']] ?? 'Inconnue'; ?></div>
                    </div>
                    <div class="buttons">
                        <div class="button-recipe">
                            <span class="material-symbols-outlined">arrow_forward</span>Recette
                        </div>
                        <div class="like">
                            <span class="material-symbols-outlined">favorite</span>
                            <?php
                            $likes = request_database($conn, "SELECT COUNT(*) AS like_count FROM likes WHERE id_recipe = " . intval($recipe['id']));
                            echo $likes->fetch_assoc()['like_count'];
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="fin-resultats">
        <div class="h1">Les résultats s'arrêtent là...</div>
        <div class="h2">Une recette manquante ? Ajoutez-là pour que tout le monde puisse en profiter !</div>
    </div>

    <?php
    include_once("includes/footer.php");
    ?>
</body>
</html>