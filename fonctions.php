<?php

// 1. Fonctions de validation (Partie 2)
function nettoyer(string $valeur): string {
    return htmlspecialchars(trim($valeur));
}

function champ_requis(string $valeur): bool {
    return !empty(trim($valeur));
}

// 2. Fonction de journalisation (Partie 3)
/**
 * Enregistre automatiquement la visite sur la page courante
 * @param PDO $pdo L'instance de connexion à la base de données
 */
function enregistrerVisite($pdo) {
    // Récupération de l'adresse IP (Gestion du proxy)
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $adresse_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $adresse_ip = $_SERVER['REMOTE_ADDR'];
    }

    // Récupération du nom de la page actuelle
    $page = basename($_SERVER['PHP_SELF']);

    // Insertion sécurisée en base de données (Requête préparée)
    try {
        $sql = "INSERT INTO visites (adresse_ip, page) VALUES (:adresse_ip, :page)";
        $stmt = $pdo->prepare($sql);
        // Regardez bien ici : on utilise bien la flèche => pour lier la clé à la variable
        $stmt->execute([
            ':adresse_ip' => $adresse_ip,
            ':page'       => $page
        ]);
    } catch (PDOException $e) {
        // Enregistrement de l'erreur en arrière-plan sans bloquer l'utilisateur
        error_log("Erreur journalisation visite : " . $e->getMessage());
    }
}