<?php
// error_reporting(E_ALL);
// ini_set('display_errors',1);

session_start();

require_once("includes/functions.php");
require_once("api/config/database.php");
require_once("includes/countries.php");

$haveToConnect = true;
$user = null;
$result = verify_token();
$user = $result['user'];

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

$recipe = null;
$author = null;

function scrap_recipe(): void {
    global $recipe;
    $db = getDatabaseConnection();
    $stmt = $db->query('SELECT * FROM recettes WHERE id = ' . $_GET['id_recipe']);
    if ($stmt->rowCount() == 0) {
        header('Location: index.php');
        exit();
    }
    $recipe = $stmt->fetch(PDO::FETCH_ASSOC);
}

function scrap_author(): void {
    global $recipe, $author;
    $db = getDatabaseConnection();
    $stmt = $db->query("SELECT pseudo FROM profils WHERE id = " . $recipe["id_author"]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $author = $result["pseudo"];
}

function difficulty(): string {
    global $recipe;
    $result = "";
    $difficulty = $recipe["difficulty"];
    if ($difficulty == 1) {
        $result = "Facile";
    } else if ($difficulty == 2) {
        $result = "Moyenne";
    } else if ($difficulty == 3) {
        $result = "Difficile";
    } else {
        $result = "Inconnue";
    }
    return "Difficulté : " . $result;
}

function convert_time($min): string {
    $days = intdiv($min, 1440);
    $min = $min % 1440;
    $hours = intdiv($min, 60);
    $minutes = $min % 60;

    $result = '';
    if ($days > 0) {
        $result .= "{$days}d ";
    }
    if ($hours > 0) {
        $result .= "{$hours}h ";
    }
    if ($minutes > 0) {
        $result .= "{$minutes}min ";
    }
    return trim($result);
}

function getLike(): bool {
    global $user;
    if (!$user || !isset($user['id'])) {
        return false;
    } else {
        $db = getDatabaseConnection();
        $stmtLikes = $db->prepare('SELECT COUNT(*) AS like_count FROM likes WHERE id_recipe = ? AND id_user = ?');
        $stmtLikes->execute([$_GET["id_recipe"], $user["id"]]);
        $like = $stmtLikes->fetch(PDO::FETCH_ASSOC);
        if ($like["like_count"] == 0) {
            return false;
        }
        return true;
    }
}

if (isset($_POST['action']) && $_POST['action'] == 'like') {
    if (!isset($user['id'])) {
        toaster("Vous n'êtes pas connecté !", "Veuillez vous connecter afin de mettre des recettes en favoris.", "error", 7);
    } else {
        $id_recette = intval($_POST['id_recipe']);
        $db = getDatabaseConnection();
        $stmt = $db->prepare('SELECT * FROM likes WHERE id_user = :id_user AND id_recipe = :id_recipe');
        $stmt->execute(['id_recipe'=> $id_recette, 'id_user' => $user['id']]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($stmt->rowCount() > 0) {
            $stmt2 = $db->prepare('DELETE FROM likes WHERE id_recipe = :result AND id_user = :user');
            $stmt2->execute(['result'=> $id_recette, 'user' => $user['id']]);
            $result2 = $stmt2->fetch(PDO::FETCH_ASSOC);
            $_SESSION['toast'] = [
                'message' => 'Like retiré pour la recette #' . $id_recette,
                'type' => 'info'
            ];
        } else {
            $stmt = $db->prepare('INSERT INTO likes (id_recipe, id_user) VALUES (:id_recipe, :id_user)');
            $stmt->execute([
                ':id_recipe' => $id_recette,
                ':id_user' => $user['id']
            ]);
            $_SESSION['toast'] = [
                'message' => 'Like enregistré pour la recette #' . $id_recette,
                'type' => 'success'
            ];
        }
        $redirectUrl = $_SERVER['PHP_SELF'];
        if (!empty($_SERVER['QUERY_STRING'])) {
            $redirectUrl .= '?' . $_SERVER['QUERY_STRING'];
        }
        header('Location: ' . $redirectUrl);
        exit();
    }
    
}

if (isset($_POST['action'])) {
    unset($_POST['action']);
}

scrap_recipe();
scrap_author();

// Afficher le toast après redirection
if (!empty($_SESSION['toast'])) {
    $t = $_SESSION['toast'];
    toaster($t['title'] ?? 'Info', $t['message'] ?? '', $t['type'] ?? 'info', $t['time'] ?? 5);
    unset($_SESSION['toast']);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($recipe) ? $recipe["title"] : "Information Manquante" ?></title>
    <link rel="stylesheet" href="assets/css/nav-foo.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:FILL@0..1" />
    <link rel="stylesheet" href="assets/css/recette.css">
    <link rel="shortcut icon" href="assets/img/icon.png" type="image/png">
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
                                <h1><?= isset($recipe) ? $recipe["title"] : "Information Manquante" ?></h1>
                                <h4>Recette par <span><?= $author ?></span></h4>
                            </div>
                        </div>
                    </div>
                    <div class="info-2">
                        <h6>DESCRIPTION</h6>
                        <p><?= isset($recipe) ? $recipe["description"] : "Information Manquante" ?></p>
                    </div>
                    <div class="info-3">
                        <div class="col">
                            <div>
                                <div class="bubble">
                                    <span class="material-symbols-outlined">stockpot</span>
                                    <p><?= isset($recipe) ? $recipe["category"] : "Information Manquante" ?></p>
                                </div>
                                <div class="bubble">
                                    <span class="material-symbols-outlined">sentiment_very_satisfied</span>
                                    <p><?= difficulty(); ?></p>
                                </div>
                                <div class="bubble">
                                    <span class="material-symbols-outlined"><img src="https://kapowaz.github.io/square-flags/flags/<?= htmlspecialchars(strtolower($recipe["origin"] ?? "")) ?>.svg" width="18px" alt="?" style="border-radius: 3px; display: flex; align-items: center; justify-content: center;"></span>
                                    <p><?= isset($recipe) ? $pays[$recipe["origin"]] : "Information Manquante"; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div>
                                <div class="bubble">
                                    <span class="material-symbols-outlined">groups</span>
                                    <p><?= $recipe["people_num"] ?? "" ?> Personnes</p>
                                </div>
                                <div class="bubble">
                                    <span class="material-symbols-outlined">countertops</span>
                                    <p>Préparation : <?= isset($recipe) ? convert_time($recipe["prep_time"]) : "Information Manquante" ?></p>
                                </div>
                                <div class="bubble">
                                    <span class="material-symbols-outlined">oven_gen</span>
                                    <p>Cuisson : <?= isset($recipe) ? convert_time($recipe["baking_time"]) : "Information Manquante" ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div style="gap: 10px;">
                                <form method="POST" action="">
                                    <input type="hidden" name="action" value="like">
                                    <input type="hidden" name="id_recipe" value="<?= htmlspecialchars($_GET['id_recipe'] ?? '') ?>">
                                    <button type="submit" class="button" style="border: none; margin: 0; padding; 0">
                                        <span class="material-symbols-outlined <?= getLike() ? 'filled' : '' ?>" style="color: <?= getLike() ? '#ff0000' : '#000' ?>;">
                                            favorite
                                        </span>
                                        <p><?= getLike() ? 'Retirer des Favoris' : 'Ajouter aux Favoris' ?></p>
                                    </button>
                                </form>
                                <div class="button">
                                    <span class="material-symbols-outlined">picture_as_pdf</span>
                                    <p>Exporter en PDF</p>
                                </div>
                                <!-- // TODO: Trouver une moyen d'exporter la page OU son contenu en pdf -->
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
                        <?php
                        $ingr = isset($recipe["ingredients"])  ? explode("<li>", $recipe["ingredients"]) : "";
                        foreach ($ingr as $ingredient) {
                            if (!empty($ingredient)) {
                                echo '<div class="liste-element">';
                                echo '<span class="material-symbols-outlined">circle</span>';
                                echo '<p>' . htmlspecialchars($ingredient) . '</p>';
                                echo '</div>';
                            }
                        }
                        ?>
                    </div>
                </div>
                <div class="part2-2">
                    <div class="titre">
                        <span class="material-symbols-outlined">list_alt</span>
                        <h3>Instructions de préparation</h3>
                    </div>
                    <div class="separateur"></div>
                    <div class="liste">
                        <?php
                        $steps = isset($recipe["instructions"]) ? explode("<li>", $recipe["instructions"]) : "";
                        $count = 1;
                        foreach ($steps as $step) {
                            if (!empty($ingredient)) {
                                echo '<div class="liste-element">';
                                echo '<p class="enumeration">' . $count . '</p>';
                                echo '<p>' . htmlspecialchars($step) . '</p>';
                                echo '</div>';
                            }
                            $count++;
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include_once("includes/footer.php"); ?>
</body>
</html>