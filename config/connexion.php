<?php 

$serveur = "localhost"; 
$utilisateur = "root"; 
$motdepasse = ""; 
$base = "tp_php"; 

try { 
    $connexion = new PDO(
        "mysql:host=$serveur;dbname=$base;charset=utf8mb4",
        $utilisateur,
        $motdepasse,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]
    );

} catch(PDOException $e) { 
    echo "Erreur : " . $e->getMessage(); 
}