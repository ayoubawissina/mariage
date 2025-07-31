<?php
$host = 'dpg-d1bik9ur433s739ktq6g-a.oregon-postgres.render.com';
$dbname = 'immobilier_945f';
$user = 'raouf';
$password = '9BdeUzrlqdXJoOcD7D7419sMRuujEI5X';

try {
    $conn = new PDO("pgsql:host=$host;port=5432;dbname=$dbname;sslmode=require", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
