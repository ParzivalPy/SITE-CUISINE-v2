<?php
function connectToDb(): mysqli
{
    $conn = mysqli_connect("mysql-cuisine.alwaysdata.net", "cuisine", "4SOpq6IU2Ke3L7", "cuisine_base");

    if (!$conn) {
        die("Echec de la connexion : " . mysqli_connect_error());
    }

    return $conn;
}

function scrapRecipe(int $num, mysqli $conn): array
{
    $sql = "SELECT * FROM `recettes` WHERE 1=1";
    $params = [];
    $types = '';

    if (isset($_POST["titre"]) && $_POST["titre"] != "") {
        $sql .= " AND `title` LIKE ?";
        $params[] = '%' . $_POST["titre"] . '%';
        $types .= 's';
    }
    if (isset($_POST["temps"]) && $_POST["temps"] != "") {
        $sql .= " AND `prep_time` <= ?";
        $params[] = $_POST["temps"];
        $types .= 'i';
    }
    if (isset($_POST["temps2"]) && $_POST["temps2"] != "") {
        $sql .= " AND `baking_time` <= ?";
        $params[] = $_POST["temps2"];
        $types .= 'i';
    }
    if (isset($_POST["personnes"]) && $_POST["personnes"] != "") {
        $sql .= " AND `people_num` = ?";
        $params[] = $_POST["personnes"];
        $types .= 'i';
    }
    if (isset($_POST["category"]) && $_POST["category"] != "") {
        $sql .= " AND `category` = ?";
        $params[] = $_POST["category"];
        $types .= 's';
    }

    $stmt = mysqli_prepare($conn, $sql);
    if ($params) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (!$result) {
        die("Erreur lors de l'exécution de la requête : " . mysqli_error($conn));
    }

    $recipes = [];
    $count = 0;

    while ($row = mysqli_fetch_assoc($result)) {
        if ($count >= $num) {
            break;
        }
        $recipes[] = $row;
        $count++;
    }

    return $recipes;
}
?>