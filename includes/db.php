<?php
require_once 'secret.php';

function connect_to_database($host, $username, $password, $dbname) {
    $conn = new mysqli($host, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}

function request_database($conn, $query) {
    $result = $conn->query($query);

    if ($result === FALSE) {
        die("Error executing query: " . $conn->error);
    }
    return $result;
}
?>