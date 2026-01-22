<?php
error_reporting(E_ALL);
ini_set('display_errors',1);
session_start();

$_SESSION['page'] = 'index.php';

require_once("includes/secret.php");
require_once("includes/functions.php");
require_once("api/config/database.php");

$haveToConnect = true;
$user = null;
$result = verify_token();
$haveToConnect = $result['haveToConnect'];
$user = $result['user'];

// Handle simple toaster action from POST (e.g. like button)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toaster') {
    $id_recipe = intval($_POST['id_recipe'] ?? 0);
    $_SESSION['toast'] = [
        'message' => 'Like enregistré pour la recette #' . $id_recipe,
        'type' => 'success'
    ];
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

function inv_pays($label) {
    global $pays;
    $inv = array_flip($pays);
    return $inv[$label] ?? '';
}

function add_origin_to_list($originCode) {
    global $pays;
    $_POST['origins-wanted'][] = $originCode;
}

function remove_origin_from_list($originCode) {
    if (isset($_POST['origins-wanted'])) {
        $index = array_search($originCode, $_POST['origins-wanted']);
        if ($index !== false) {
            unset($_POST['origins-wanted'][$index]);
            $_POST['origins-wanted'] = array_values($_POST['origins-wanted']);
        }
    }
}

if (isset($_POST['origin'])) {
    $origin = $_POST['origin'];
    $queryParams = [];
    $inv = array_flip($pays);
    if (!empty($origin)) {
        $queryParams['origin'] = $inv[$origin] ?? '';
    }
    $queryString = http_build_query($queryParams);
    header("Location: index.php" . (!empty($queryString) ? "?" . $queryString : ""));
    exit();
} else if (isset($_POST['action']) && $_POST['action'] == 'like') {
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

    // Post/Redirect/Get to prevent form re-execution on refresh
    $redirectUrl = $_SERVER['PHP_SELF'];
    if (!empty($_SERVER['QUERY_STRING'])) {
        $redirectUrl .= '?' . $_SERVER['QUERY_STRING'];
    }
    header('Location: ' . $redirectUrl);
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr-FR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gab'recettes</title>
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/nav-foo.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:FILL@0..1" />
</head>
<body>
    <?php
    include_once("includes/navbar.php");

    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $conn = getDatabaseConnection();
    // Use PDO prepared statements to avoid non-existing real_escape_string and prevent SQL injection
    $sql = "SELECT r.*, p.pseudo AS author_pseudo FROM recettes r LEFT JOIN profils p ON r.id_author = p.id WHERE 1=1";
    $params = [];

    if ($search !== '') {
        $sql .= " AND r.title LIKE :search";
        $params[':search'] = '%' . $search . '%';
    }

    if (isset($_GET['origin']) && !empty($_GET['origin'])) {
        $sql .= " AND r.origin = :origin";
        $params[':origin'] = $_GET['origin'];
    }

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <div class="menu">
        <div class="header">
            <h1>Trouvez le plat <span>parfait</span></h1>
        </div>
        <div class="research-zone">
            <div class="research-bar">
                <span class="material-symbols-outlined">search</span>
                <form action="index.php" method="get">
                    <input type="text" placeholder="Chercher une recette" id="search" name="search" value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>">
                </form>
            </div>
            <div class="indications">
                Essayez: <span>'blanquette de veau'</span> ou <span>'tarte aux pommes'</span>
            </div>
        </div>
        <div class="filters-zone">
            <div class="filters">
                <div class="filter">
                    <p>Type</p>
                    <span class="material-symbols-outlined">keyboard_arrow_down</span>
                    <input type="checkbox" class="filter-checkbox" id="filter-type-checkbox">
                </div>
                <div class="filter">
                    <p>Temps de préparation</p>
                    <span class="material-symbols-outlined">keyboard_arrow_down</span>
                    <input type="checkbox" class="filter-checkbox" id="filter-prep-time-checkbox">
                </div>
                <div class="filter">
                    <p>Origine</p>
                    <span class="material-symbols-outlined">keyboard_arrow_down</span>
                </div>
            </div>

            <div class="filter-extend" id="filter-type-extend" style="display: none;">
                <div class="filter-title">Filtrer par Origine</div>
                    <form action="index.php" method="post" id="origin-filter-form" style="width: 100%; display: flex; flex-direction: column; gap: 15px;">
                        <div class="research-origins">
                            <span class="material-symbols-outlined">search</span>
                            <input id="origin-input" placeholder="Chercher une origine" list="origin" name="origin" value="">
                            <datalist id="origin">
                                <?php
                                include_once("includes/secret.php");
                                
                                foreach ($pays as $code => $label):
                                    $selected = (isset($_GET['origin']) && $_GET['origin'] === $code) ? 'selected' : '';
                                    echo '<option>' . htmlspecialchars($label, ENT_QUOTES) . '</option>';
                                endforeach;
                                ?>
                            </datalist>
                        </div>
                        <div class="proposed-origins categories" style="display: none;">
                            <?php
                            if (isset($_POST['origins-wanted'])):
                                foreach ($_POST['origins-wanted'] as $originCode):
                                    echo "<script>add_origin_tag(" . json_encode($originCode) . ");</script>";
                                endforeach;
                            endif;
                            ?>
                        </div>
                        <div class="filter-buttons">
                            <div class="clear-button" id="clear-origin-filter" style="cursor: pointer;" onclick="window.location.href='index.php';">
                                <span class="material-symbols-outlined">filter_alt_off</span>Supprimer
                            </div>
                            <div class="apply-button" id="apply-origin-filter" style="cursor: pointer;" onclick="this.closest('form').submit();">
                                <span class="material-symbols-outlined">check</span>Valider
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="filter-extend" id="filter-prep-time-extend" style="display: none;">
                <div class="filter-title">Filtrer par Origine</div>
                    <div class="research-origins">
                        <span class="material-symbols-outlined">search</span>
                        <input id="origin-input" placeholder="Chercher une origine" list="origin" name="origin" value="<?php echo isset($_GET['origin']) ? htmlspecialchars($_GET['origin'], ENT_QUOTES, 'UTF-8') : ''; ?>" multiple>
                        <datalist id="origin">
                            <?php
                            include_once("includes/secret.php");
                            
                            foreach ($pays as $code => $label):
                                $selected = (isset($_GET['origin']) && $_GET['origin'] === $code) ? 'selected' : '';
                                echo '<option>' . htmlspecialchars($label, ENT_QUOTES) . '</option>';
                            endforeach;
                            ?>
                        </datalist>
                    </div>
                    <div class="proposed-origins">
                    </div>
                    <div class="filter-buttons">
                        <div class="clear-button">
                            <span class="material-symbols-outlined">filter_alt_off</span>Supprimer
                        </div>
                        <div class="apply-button">
                            <span class="material-symbols-outlined">check</span>Valider
                        </div>
                    </div>
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
                        <div class="title"><?php echo htmlspecialchars($recipe['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?></div>
                        <div class="author">Recette par <span>
                        <?php 
                        echo htmlspecialchars($recipe['author_pseudo'] ?? 'Auteur inconnu', ENT_QUOTES, 'UTF-8');
                        ?></span></div>
                    </div>
                    <div class="time"><?php echo intval($recipe['prep_time']); ?>'</div>
                </div>
                <div class="down">
                    <div class="categories">
                        <div class="categorie"><span class="material-symbols-outlined">stockpot</span><p><?php echo htmlspecialchars($recipe['category'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p></div>
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
                        <div class="categorie"><img src="https://kapowaz.github.io/square-flags/flags/<?php echo htmlspecialchars(strtolower($recipe['origin'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>.svg" width="20" alt="?" style="border-radius: 3px; margin: 2px; display: flex; align-items: center; justify-content: center;"/><?php echo htmlspecialchars($pays[$recipe['origin']] ?? 'Inconnue', ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                    <div class="buttons">
                        <div class="button-recipe">
                            <span class="material-symbols-outlined">arrow_forward</span>Recette
                        </div>
                        <div class="like">
                            <?php
                            $db = getDatabaseConnection();
                            if (isset($user['id'])) {
                                $stmtNumLikes = $db->prepare("SELECT COUNT(*) AS count FROM likes WHERE id_user = :user AND id_recipe = :recipe");
                                $stmtNumLikes->execute([
                                    ':user' => $user['id'],
                                    ':recipe' => $recipe['id']
                                ]);
                                $num_likes = (int) $stmtNumLikes->fetchColumn();
                            }
                            ?>
                            <form method="POST">
                                <input type="hidden" name="action" value="like">
                                <input type="hidden" name="id_recipe" value="<?php echo intval($recipe['id']); ?>">
                                <button type="submit" style="color: #000000; background: none; border: none; cursor: pointer; padding: 0; display: flex; align-items: center; justify-content: center;" <?= !isset($user) ? "disabled" : "" ?>>
                                    <span class="material-symbols-outlined <?= (isset($num_likes) && $num_likes > 0) ? 'filled" style="color: #ff0000;' : ''?>">favorite</span>
                                </button>
                            </form>
                            <?php
                            // Use PDO prepared statement to fetch the like count
                            $stmtLikes = $db->query('SELECT COUNT(*) AS like_count FROM likes WHERE id_recipe = ' . intval($recipe['id']));
                            $likeRow = $stmtLikes->fetch(PDO::FETCH_ASSOC);
                            echo intval($likeRow['like_count'] ?? 0);
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

    <script>
        <?php 
        if (isset($_POST['origins-wanted'])) {
            $originsSearched = json_encode($_POST['origins-wanted'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
            echo "let originsSearched = " . $originsSearched . ";";
        } else {
            echo "let originsSearched = [];";
        }
        echo "console.log('originsSearched:', originsSearched);";
        ?>

        const _ftc = document.getElementById('filter-type-checkbox');
        const filterExtend = document.getElementById('filter-type-extend');

        if (_ftc && filterExtend) {
            _ftc.addEventListener('change', function() {
                filterExtend.style.display = _ftc.checked ? 'flex' : 'none';
            });
        }

        const paysLabels = <?php echo json_encode(array_values($pays), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        const paysMap = <?php echo json_encode(array_flip($pays), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        const input = document.getElementById('origin-input');
        const proposed = document.querySelector('.proposed-origins');

        function escapeHtml(str){
            return str.replace(/[&<>"']/g, function(m){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]; });
        }

        if (input) {
            input.addEventListener('input', function(){
            if (this.value != "") {
                this.placeholder = "";
            } else {
                this.placeholder = "Chercher une origine";
            }
            
            const val = this.value.trim();
            if (val !== '' && paysLabels.indexOf(paysMap[val]) !== -1) {
                if (proposed) {
                proposed.style.display = 'flex';
                }

                const originsWanted = <?php echo json_encode($_POST['origins-wanted'] ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
                const codeForVal = paysMap[val] || val;

                if (originsWanted.indexOf(val) !== -1 || originsWanted.indexOf(codeForVal) !== -1) {
                console.log('Origine déjà présente dans $_POST[\"origins-wanted\"]');
                return;
                } else {
                
                //create the origin country tag
                let div = document.createElement('div');
                div.className = 'proposed-origin-item categorie';
                div.style.cursor = 'pointer';

                let img = document.createElement('img');
                const code = paysMap[val] ? paysMap[val].toLowerCase() : '';
                img.src = 'https://kapowaz.github.io/square-flags/flags/' + code + '.svg';
                
                img.className = 'img';
                img.width = 20;
                img.alt = '?';
                img.style = 'border-radius: 3px; margin: 2px; display: flex; align-items: center; justify-content: center;';
                div.appendChild(img);

                let span = document.createElement('span');
                span.textContent = val;

                //add tag to html
                div.appendChild(span);
                proposed.appendChild(div);

                originsWanted.push(codeForVal);
                console.log('Updated originsWanted:', originsWanted);

                document.getElementById('origin-filter-form').reset();
                }

                
            }
            });
        }

        // optional: click on proposed item fills input (and keeps it visible)
        if (proposed) {
            proposed.addEventListener('click', function(e){
                const item = e.target.closest('.proposed-origin-item');
                if (item && input) {
                    input.value = item.textContent;
                    proposed.style.display = 'flex';
                }
            });
        }
    </script>
</body>
</html>