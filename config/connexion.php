<?php 

$serveur = "sql311.infinityfree.com"; 
$utilisateur = "if0_43050163"; 
$motdepasse = "HEMIP2026"; 
$base = "if0_43050163_hemip"; 

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