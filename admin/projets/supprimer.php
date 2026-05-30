<?php
session_start();
require_once '../../config/connexion.php';

// 1. Vérification de la session
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../connexion.php');
    exit();
}

// 2. Vérification stricte de la méthode POST et du jeton CSRF (Exigence sécurité)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Attaque CSRF bloquée.");
    }

    $id = intval($_POST['id'] ?? 0);

    if ($id > 0) {
        try {
            // 3. Récupérer le nom de l'image pour la supprimer du dossier
            $stmtImg = $pdo->prepare("SELECT image FROM projets WHERE id = :id");
            $stmtImg->execute([':id' => $id]);
            $projet = $stmtImg->fetch();

            if ($projet && !empty($projet['image'])) {
                $cheminImage = '../../images/projets/' . $projet['image'];
                if (file_exists($cheminImage)) {
                    unlink($cheminImage); // Supprime la photo du serveur
                }
            }

            // 4. Supprimer le projet de la base de données
            $stmtDelete = $pdo->prepare("DELETE FROM projets WHERE id = :id");
            $stmtDelete->execute([':id' => $id]);

        } catch (PDOException $e) {
            error_log("Erreur suppression projet : " . $e->getMessage());
        }
    }
}

// Redirection instantanée vers la liste des projets
header('Location: index.php');
exit();