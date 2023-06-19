<?php
    try {
        require_once 'config.php';
        $conn = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
    } catch (Exception $e) {
        echo $e->getMessage();
        echo "<br>";
        echo $e->getCode();
    }
?>