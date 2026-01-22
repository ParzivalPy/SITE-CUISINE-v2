<?php
session_start();

require_once("includes/functions.php");
require_once("api/config/database.php");

$_SESSION["page"] = "index.php";

$haveToConnect = true;
$user = null;
$result = verify_token();
$haveToConnect = $result['haveToConnect'];
$user = $result['user'];

if (!isset($_GET['id_recipe'])) {
    header('Location: index.php');
    exit();
}

function scrap_recipe() {
    $db = getDatabaseConnection();
    $stmt = $db->query('SELECT * FROM recettes WHERE id = ' . $_GET['id_recipe']);
    if ($stmt->rowCount() == 0) {
        return ['success' => false,'message'=> 'No recipe finded'];
    }
    $recipe = $stmt->fetch(PDO::FETCH_ASSOC);
}

scrap_recipe();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $recipe["title"] ?></title>
    <link rel="stylesheet" href="assets/css/nav-foo.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:FILL@0..1" />
    <link rel="stylesheet" href="assets/css/recette.css">
</head>
<body>
    <?php include_once("includes/navbar.php"); ?>
    <div class="pre-body">
        <div class="body">
            <div class="part1">
                <div class="img"></div>
                <div class="infos">
                    <div class="info-1">
                        <div>
                            <div class="titre-et-sous-titre">
                                <h1>L'Incroyable Cookie</h1>
                                <h4>Recette par <span>Titilapierre</span></h4>
                            </div>
                        </div>
                    </div>
                    <div class="info-2">
                        <h6>DESCRIPTION</h6>
                        <p>Des cookies dorés à l’extérieur et moelleux à l’intérieur, délicatement parfumés à la vanille et généreusement garnis de pépites de chocolat. Une recette simple et rapide pour un moment gourmand à partager (ou pas).</p>
                    </div>
                    <div class="info-3">
                        <div class="col">
                            <div>
                                <div class="bubble">
                                    <span class="material-symbols-outlined">stockpot</span>
                                    <p>Dessert</p>
                                </div>
                                <div class="bubble">
                                    <span class="material-symbols-outlined">sentiment_very_satisfied</span>
                                    <p>Difficulté : Facile</p>
                                </div>
                                <div class="bubble">
                                    <span class="material-symbols-outlined"><img src="https://kapowaz.github.io/square-flags/flags/it.svg" alt="" width="18px" alt="?" style="border-radius: 3px; display: flex; align-items: center; justify-content: center;"></span>
                                    <p>Italie</p>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div>
                                <div class="bubble">
                                    <span class="material-symbols-outlined">groups</span>
                                    <p>6 Personnes</p>
                                </div>
                                <div class="bubble">
                                    <span class="material-symbols-outlined">countertops</span>
                                    <p>Préparation : 15 min</p>
                                </div>
                                <div class="bubble">
                                    <span class="material-symbols-outlined">oven_gen</span>
                                    <p>Cuisson : 10 min</p>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div style="gap: 10px;">
                                <div class="button">
                                    <span class="material-symbols-outlined">favorite</span>
                                    <p>Ajouter aux Favoris</p>
                                </div>
                                <div class="button">
                                    <span class="material-symbols-outlined">picture_as_pdf</span>
                                    <p>Exporter en PDF</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="part2">
                <div class="part2-1">
                    <div class="titre">
                        <span class="material-symbols-outlined">shopping_basket</span>
                        <h3>Liste des ingrédients</h3>
                    </div>
                    <div class="separateur"></div>
                    <div class="liste">
                        <div class="liste-element">
                            <span class="material-symbols-outlined">circle</span>
                            <p>1 oeuf</p>
                        </div>
                        <div class="liste-element">
                            <span class="material-symbols-outlined">circle</span>
                            <p>85 g de sucre </p>
                        </div>
                        <div class="liste-element">
                            <span class="material-symbols-outlined">circle</span>
                            <p>85 g de beurre doux </p>
                        </div>
                        <div class="liste-element">
                            <span class="material-symbols-outlined">circle</span>
                            <p>150 g de farine </p>
                        </div>
                        <div class="liste-element">
                            <span class="material-symbols-outlined">circle</span>
                            <p>100 g de pépites de chocolat </p>
                        </div>
                        <div class="liste-element">
                            <span class="material-symbols-outlined">circle</span>
                            <p>1 sachet de sucre vanillé </p>
                        </div>
                        <div class="liste-element">
                            <span class="material-symbols-outlined">circle</span>
                            <p>1 càc de levure chimique </p>
                        </div>
                        <div class="liste-element">
                            <span class="material-symbols-outlined">circle</span>
                            <p>1/2 càc de sel</p>
                        </div>
                    </div>
                </div>
                <div class="part2-2">
                    <div class="titre">
                        <span class="material-symbols-outlined">list_alt</span>
                        <h3>Instructions de préparation</h3>
                    </div>
                    <div class="separateur"></div>
                    <div class="liste">
                        <div class="liste-element">
                            <p class="enumeration">1.</p>
                            <p>Laisser ramollir le beurre à température ambiante. Dans un saladier, le malaxer avec le sucre.</p>
                        </div>
                        <div class="liste-element">
                            <p class="enumeration">2.</p>
                            <p>Ajouter l'oeuf et éventuellement le sucre vanillé.</p>
                        </div>
                        <div class="liste-element">
                            <p class="enumeration">3.</p>
                            <p>Verser progressivement la farine, la levure chimique, le sel, et les pépites de chocolat. Bien mélanger.</p>
                        </div>
                        <div class="liste-element">
                            <p class="enumeration">4.</p>
                            <p>Beurrer une plaque allant au four ou la recouvrir d'une plaque de  silicone. À l'aide de deux cuillères à soupe ou simplement avec les  mains, former des noix de pâte en les espaçant car elles s'étaleront à  la cuisson. </p>
                        </div>
                        <div class="liste-element">
                            <p class="enumeration">5.</p>
                            <p>Faire cuire 8 à 10 minutes à 180°C soit thermostat.</p>
                        </div>
                        <div class="liste-element">
                            <p class="enumeration">6.</p>
                            <p>Il faut les sortir dès que les contours commencent à brunir.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include_once("includes/footer.php"); ?>
</body>
</html>