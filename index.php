<?php
require_once(__DIR__ . '/includes/db.php');
?>

<!DOCTYPE html>
<html lang="fr-FR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gab'recettes</title>
    <link rel="stylesheet" href="assets\css\style.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
</head>
<body>
    <?php
    include_once("includes/navbar.php");
    ?>
    <div class="headers">
        <h1>Trouvez le plat <span>parfait</span></h1>

        <div class="search">
            <div class="bar">
                <span class="material-symbols-outlined">search</span>
                <input type="text" placeholder="Chercher une recette">
            </div>
            <div class="examples">
                Essayez: <span>‘blanquette de veau’</span> ou <span>‘tarte aux pommes’</span>
            </div>
        </div>

        <div class="filters">
            <div class="type box">
                <span class="filter-text">Type</span>
                <div class="google-icon-container filters-icon">
                    <span class="material-symbols-outlined">keyboard_arrow_down</span>
                </div>
            </div>
            <div class="time box">
                <span class="filter-text">Temps de préparation</span>
                <div class="google-icon-container filters-icon">
                    <span class="material-symbols-outlined">keyboard_arrow_down</span>
                </div>
            </div>
            <div class="origin box">
                <span class="filter-text">Origine</span>
                <div class="google-icon-container filters-icon">
                    <span class="material-symbols-outlined">keyboard_arrow_down</span>
                </div>
            </div>
        </div>
    </div>
    <div class="recipes">
        <?php
        $conn = connectToDb();
        $recipes = scrapRecipe(10, $conn);
        foreach ($recipes as $recipe):
        ?>
        <div class="recipe-template">
            <img src="assets\images\PXL_20251017_121440361.jpg">
            <div class="informations">
                <div class="informations-up">
                    <div class="titre">
                        <div>
                            <?php echo $recipe['title']; ?>
                        </div>
                        <div>
                            Recette par <span>John Doe</span>
                        </div>
                    </div>
                    <div class="preparation-time">
                        20'
                    </div>
                </div>
                <div class="informations-down">

                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php
    include_once("includes/footer.php");
    ?>
</body>
</html>