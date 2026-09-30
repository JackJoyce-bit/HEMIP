<?php 
 
$serveur = "localhost"; 
$utilisateur = "root"; 
$motdepasse = ""; 
$base = "tp_php"; 
 
try { 
$connexion = new PDO("mysql:host=$serveur;dbname=$base",$utilisateur,$motdepasse); 
$connexion->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION); 
} catch(PDOException $e){ 
echo "Erreur : " . $e->getMessage(); 
}