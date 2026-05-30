<?php
session_start();

// Écrase le tableau de session
$_SESSION = [];

// Détruit la session côté serveur
session_destroy();

// Redirection immédiate vers la page de connexion publique de l'admin
header('Location: connexion.php');
exit();