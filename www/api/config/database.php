<?php

declare(strict_types=1);

// TODO: a modifier lors de la prod
// dev : localhost
// prod : mysql-cuisine.alwaysdata.net

$config = [
    'host' => 'mysql-cuisine.alwaysdata.net',
    'username' => 'cuisine',
    'password' => '4SOpq6IU2Ke3L7',
    'database' => 'cuisine_base',
];

$dsn = "mysql:host={$config['host']};dbname={$config['database']}";

function getDatabaseConnection(): PDO {
    global $dsn, $config;
    try {
        $databaseConnection = new PDO($dsn, $config['username'], $config['password']);
        $databaseConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $databaseConnection;
    } catch(PDOException $error) {
        echo "Connection failed: " . $error->getMessage();
        exit;
    }
}



?>