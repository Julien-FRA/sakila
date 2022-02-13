<?php

try {
    // Connexion à la BDD

    $host = '163.172.130.142';
    $db = 'sakila';
    $username = 'etudiant';
    $password = 'CrERP29qwMNvcbnAMgLzW9CwuTC5eJHn';
    $port = '3310';
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset;port=$port";

    $pdo = new PDO($dsn, $username, $password);
} catch (PDOException $e) {
    echo 'Erreur : ' . $e->getMessage();
    die();
}
