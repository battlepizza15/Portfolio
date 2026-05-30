<?php
// 1. Demarrage de la session et verification d acces
session_start();
require_once '../config/connexion.php';
require_once '../fonctions.php';

// Si l utilisateur n est pas connecte, redirection immediate
if (!isset($_SESSION['admin_id'])) {
    header('Location: connexion.php');
    exit();
}

try {
    // ==========================================================
    // RECUPERATION DES COMPTEURS STATISTIQUES
    // ==========================================================

    // Nombre total de projets publies
    $stmtProjets = $pdo->query("SELECT COUNT(*) FROM projets");
    $totalProjets = $stmtProjets->fetchColumn();

    // Nombre de messages de contact non lus
    $stmtMessages = $pdo->query("SELECT COUNT(*) FROM messages_contact WHERE lu = 0");
    $totalMessagesNonLus = $stmtMessages->fetchColumn();

    // Nombre de demandes de projet non lues
    $stmtDemandes = $pdo->query("SELECT COUNT(*) FROM demandes_projet WHERE lu = 0");
    $totalDemandesNonLus = $stmtDemandes->fetchColumn();

    // ==========================================================
    // RECUPERATION LES REQUETES DE LISTE (LIMIT 5)
    // ==========================================================

    // Les 5 dernieres visites
    $stmtLastVisites = $pdo->query("SELECT adresse_ip, page, date_visite FROM visites ORDER BY date_visite DESC LIMIT 5");
    $dernieresVisites = $stmtLastVisites->fetchAll();

    // Les 5 dernieres demandes de projet
    $stmtLastDemandes = $pdo->query("SELECT nom, email, type_projet, date_demande FROM demandes_projet ORDER BY date_demande DESC LIMIT 5");
    $dernieresDemandes = $stmtLastDemandes->fetchAll();

} catch (PDOException $e) {
    error_log("Erreur Dashboard : " . $e->getMessage());
    die("Une erreur technique est survenue lors du chargement du tableau de bord.");
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Administration</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; margin: 0; background: #f4f6f9; color: #333; }
        .navbar { background: #2c3e50; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .navbar h1 { margin: 0; font-size: 20px; }
        .navbar a { color: #ecf0f1; text-decoration: none; background: #e74c3c; padding: 8px 15px; border-radius: 4px; }
        .navbar a:hover { background: #c0392b; }
        .container { padding: 30px; max-width: 1200px; margin: 0 auto; }
        .welcome { margin-bottom: 30px; }
        
        /* Grille des compteurs */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); border-left: 5px solid #3498db; }
        .card.projets { border-left-color: #2ecc71; }
        .card.messages { border-left-color: #f1c40f; }
        .card.demandes { border-left-color: #9b59b6; }
        .card h3 { margin: 0 0 10px 0; color: #7f8c8d; font-size: 14px; text-transform: uppercase; }
        .card .number { font-size: 32px; font-weight: bold; color: #2c3e50; }
        
        /* Grille des tableaux */
        .tables-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(500px, 1fr)); gap: 30px; }
        .table-box { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .table-box h2 { margin-top: 0; font-size: 18px; color: #2c3e50; border-bottom: 2px solid #ecf0f1; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { text-align: left; padding: 12px; border-bottom: 1px solid #ddd; font-size: 14px; }
        th { background: #f8f9fa; color: #34495e; }
        tr:hover { background: #fdfefe; }
        .text-muted { color: #95a5a6; font-style: italic; }
    </style>
</head>
<body>

<div class="navbar">
    <h1>Portfolio Moise Bienvenue - Tableau de bord</h1>
    <div>
        <span>Bienvenue, <?php echo htmlspecialchars($_SESSION['Moise Bienvenue'] ?? 'Admin'); ?> ! </span>&nbsp;&nbsp;
        <a href="deconnexion.php">Déconnexion</a>
    </div>
</div>

<div class="container">
    <div class="welcome">
        <h2>Vue d'ensemble de l'activité</h2>
    </div>

    <div class="stats-grid">
        <div class="card projets">
            <h3>Projets Publiés</h3>
            <div class="number"><?php echo $totalProjets; ?></div>
        </div>
        <div class="card messages">
            <h3>Messages non lus</h3>
            <div class="number"><?php echo $totalMessagesNonLus; ?></div>
        </div>
        <div class="card demandes">
            <h3>Demandes de projet non lues</h3>
            <div class="number"><?php echo $totalDemandesNonLus; ?></div>
        </div>
    </div>

    <div class="tables-grid">
        
        <div class="table-box">
            <h2>Les 5 dernières visites</h2>
            <?php if (!empty($dernieresVisites)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Adresse IP</th>
                            <th>Page visitée</th>
                            <th>Date & Heure</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dernieresVisites as $visite): ?>
                            <tr>
                                <td><code><?php echo htmlspecialchars($visite['adresse_ip']); ?></code></td>
                                <td><?php echo htmlspecialchars($visite['page']); ?></td>
                                <td><?php echo htmlspecialchars($visite['date_visite']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-muted">Aucune visite enregistrée pour le moment.</p>
            <?php endif; ?>
        </div>

        <div class="table-box">
            <h2>Les 5 dernières demandes de projet</h2>
            <?php if (!empty($dernieresDemandes)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Type de projet</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dernieresDemandes as $demande): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($demande['nom']); ?></td>
                                <td><?php echo htmlspecialchars($demande['type_projet']); ?></td>
                                <td><?php echo htmlspecialchars($demande['date_demande']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-muted">Aucune demande reçue pour le moment.</p>
            <?php endif; ?>
        </div>

    </div>
</div>

</body>
</html>