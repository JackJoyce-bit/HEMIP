<?php
session_start();

// Supprimer toutes les données de session
$_SESSION = [];

// Détruire complètement la session
session_unset();
session_destroy();

// Empêcher le navigateur de conserver les pages en cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

// Retour vers la page d'accueil
header("Location: index.php");
exit();
?>