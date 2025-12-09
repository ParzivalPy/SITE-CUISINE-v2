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
    ?>
    <div class="menu">
        <div class="header">
            <h1>Trouvez le plat <span>parfait</span></h1>
        </div>
        <div class="research-zone">
            <div class="research-bar">
                <span class="material-symbols-outlined">search</span>
                <form action="index.php" method="get">
                    <input type="text" placeholder="Chercher une recette">
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
        <div class="recipe">
            <div class="photo"></div>
            <div class="informations">
                <div class="up">
                    <div class="titleandauthor">
                        <div class="title">L'authentique Carbonara</div>
                        <div class="author">Recette par <span>John Doe</span></div>
                    </div>
                    <div class="time">20'</div>
                </div>
                <div class="down">
                    <div class="categories">
                        <div class="categorie"><span class="material-symbols-outlined">stockpot</span>Plat</div>
                        <div class="categorie"><span class="material-symbols-outlined">sentiment_very_satisfied</span>Difficulté : Facile</div>
                        <div class="categorie"><span class="material-symbols-outlined">crop_square</span>Italie</div>
                        <div class="categorie-plus">+3</div>
                    </div>
                    <div class="buttons">
                        <div class="button-recipe">
                            <span class="material-symbols-outlined">arrow_forward</span>Recette
                        </div>
                        <div class="like">
                            <span class="material-symbols-outlined">favorite</span>35
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="recipe">2</div>
        <div class="recipe">3</div>
        <div class="recipe">4</div>
        <div class="recipe">5</div>
        <div class="recipe">6</div>
        <div class="recipe">7</div>
        <div class="recipe">8</div>
        <div class="recipe">9</div>
        <div class="recipe">10</div>
        <div class="recipe">11</div>
        <div class="recipe">12</div>
    </div>

    <?php
    include_once("includes/footer.php");
    ?>
</body>
</html>