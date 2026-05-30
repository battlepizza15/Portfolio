<?php
// 1. Démarrage de la session et vérification des droits
session_start();
// On remonte de deux dossiers ('../../') pour atteindre la configuration
require_once '../../config/connexion.php';
require_once '../../fonctions.php';

// Sécurité : Vérification que l'admin est connecté
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../connexion.php');
    exit();
}

// Génération du jeton CSRF pour sécuriser les futurs boutons de suppression
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$token = $_SESSION['csrf_token'];

try {
    // 2. Récupération de tous les projets triés par date décroissante (Exigence 5.3)
    $sql = "SELECT * FROM projets ORDER BY date_creation DESC";
    $stmt = $pdo->query($sql);
    $projets = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Erreur Liste Projets : " . $e->getMessage());
    $erreur = "Impossible de charger les projets.";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Projets - Admin</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; margin: 0; background: #f4f6f9; color: #333; }
        .navbar { background: #2c3e50; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .navbar h1 { margin: 0; font-size: 20px; }
        .navbar a { color: #ecf0f1; text-decoration: none; }
        .btn-retour { background: #7f8c8d; color: white; padding: 8px 15px; border-radius: 4px; text-decoration: none; }
        .container { padding: 30px; max-width: 1100px; margin: 0 auto; }
        .header-action { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .btn-ajouter { background: #2ecc71; color: white; padding: 10px 20px; border-radius: 4px; text-decoration: none; font-weight: bold; }
        .btn-ajouter:hover { background: #27ae60; }
        
        table { width: 100%; background: white; border-collapse: collapse; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eeeeee; }
        th { background: #34495e; color: white; font-size: 14px; text-transform: uppercase; }
        tr:hover { background: #f9f9f9; }
        
        .img-preview { width: 60px; height: 40px; object-fit: cover; border-radius: 4px; background: #eee; }
        .actions { display: flex; gap: 10px; }
        .btn-modifier { background: #3498db; color: white; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 13px; }
        .btn-modifier:hover { background: #2980b9; }
        .btn-supprimer { background: #e74c3c; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 13px; }
        .btn-supprimer:hover { background: #c0392b; }
        .text-muted { color: #7f8c8d; font-style: italic; }
    </style>
</head>
<body>

<div class="navbar">
    <h1><a href="../dashboard.php" style="color:white;">Zone Admin</a> &raquo; Gestion des Projets</h1>
    <a href="../dashboard.php" class="btn-retour">Retour au Dashboard</a>
</div>

<div class="container">
    <div class="header-action">
        <h2>Liste de vos projets publics</h2>
        <a href="ajouter.php" class="btn-ajouter">+ Ajouter un projet</a>
    </div>

    <?php if (isset($erreur)): ?>
        <p style="color:red;"><?php echo htmlspecialchars($erreur); ?></p>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>Image</th>
                <th>Titre</th>
                <th>Technologies</th>
                <th>Date de création</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($projets)): ?>
                <?php foreach ($projets as $projet): ?>
                    <tr>
                        <td>
                            <?php if (!empty($projet['image'])): ?>
                                <img src="../../images/projets/<?php echo htmlspecialchars($projet['image']); ?>" class="img-preview" alt="Projet">
                            <?php else: ?>
                                <span class="text-muted">Aucune</span>
                            <?php endif; ?>
                        </td>
                        <td><strong><?php echo htmlspecialchars($projet['titre']); ?></strong></td>
                        <td><code><?php echo htmlspecialchars($projet['technologies']); ?></code></td>
                        <td><?php echo htmlspecialchars($projet['date_creation']); ?></td>
                        <td class="actions">
                            <a href="modifier.php?id=<?php echo $projet['id']; ?>" class="btn-modifier">Modifier</a>
                            
                            <form action="supprimer.php" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer définitivement ce projet ?');" style="margin:0;">
                                <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
                                <input type="hidden" name="id" value="<?php echo $projet['id']; ?>">
                                <button type="submit" class="btn-supprimer">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-muted" style="text-align: center; padding: 30px;">
                        Aucun projet en base de données pour le moment. Cliquez sur le bouton vert pour en créer un !
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>