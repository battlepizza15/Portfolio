<?php
session_start();
// On remonte de deux dossiers ('../../') pour atteindre la configuration
require_once '../../config/connexion.php';
require_once '../../fonctions.php';

// Sécurité : Vérification que l'admin est connecté
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../connexion.php');
    exit();
}

// Gestion du jeton CSRF pour l'action "Marquer comme lu"
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$token = $_SESSION['csrf_token'];

// Action : Marquer comme lu si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Attaque CSRF bloquée.");
    }

    $id = intval($_POST['id'] ?? 0);
    $type = $_POST['type'] ?? ''; // 'contact' ou 'projet'

    if ($id > 0) {
        try {
            if ($type === 'contact') {
                $stmt = $pdo->prepare("UPDATE messages_contact SET lu = 1 WHERE id = :id");
            } elseif ($type === 'projet') {
                $stmt = $pdo->prepare("UPDATE demandes_projet SET lu = 1 WHERE id = :id");
            }
            $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log("Erreur mise à jour message lu : " . $e->getMessage());
        }
    }
}

try {
    // Récupération des messages de contact non lus en premier, puis les lus
    $messagesContact = $pdo->query("SELECT * FROM messages_contact ORDER BY lu ASC, date_envoi DESC")->fetchAll();

    // Récupération des demandes de projet
    $demandesProjet = $pdo->query("SELECT * FROM demandes_projet ORDER BY lu ASC, date_demande DESC")->fetchAll();
} catch (PDOException $e) {
    die("Erreur lors de la récupération des messages : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Boîte de réception - Admin</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; margin: 0; background: #f4f6f9; color: #333; }
        .navbar { background: #2c3e50; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .navbar h1 { margin: 0; font-size: 20px; }
        .navbar a { color: #ecf0f1; text-decoration: none; }
        .btn-retour { background: #7f8c8d; color: white; padding: 8px 15px; border-radius: 4px; text-decoration: none; }
        .container { padding: 30px; max-width: 1200px; margin: 0 auto; }
        
        .section-box { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); margin-bottom: 40px; }
        .section-box h2 { margin-top: 0; color: #2c3e50; border-bottom: 2px solid #f4f6f9; padding-bottom: 10px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; font-size: 14px; }
        th { background: #f8f9fa; color: #34495e; }
        
        .statut { padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .non-lu { background: #ffe3e3; color: #c10000; }
        .lu { background: #d4edda; color: #155724; }
        
        .btn-lu { background: #2ecc71; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 13px; }
        .btn-lu:hover { background: #27ae60; }
        .text-muted { color: #95a5a6; font-style: italic; }
        .msg-contenu { max-width: 300px; word-wrap: break-word; background: #fdfefe; padding: 8px; border-radius: 4px; border: 1px dashed #ddd; }
    </style>
</head>
<body>

<div class="navbar">
    <h1><a href="../dashboard.php" style="color:white;">Zone Admin</a> &raquo; Boîte de réception</h1>
    <a href="../dashboard.php" class="btn-retour">Retour au Dashboard</a>
</div>

<div class="container">

    <div class="section-box">
        <h2>Messages de Contact</h2>
        <?php if (!empty($messagesContact)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Statut</th>
                        <th>Expéditeur</th>
                        <th>Email</th>
                        <th>Message</th>
                        <th>Date d'envoi</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($messagesContact as $msg): ?>
                        <tr>
                            <td>
                                <span class="statut <?php echo $msg['lu'] ? 'lu' : 'non-lu'; ?>">
                                    <?php echo $msg['lu'] ? 'Lu' : 'Nouveau'; ?>
                                </span>
                            </td>
                            <td><strong><?php echo htmlspecialchars($msg['nom']); ?></strong></td>
                            <td><a href="mailto:<?php echo htmlspecialchars($msg['email']); ?>"><?php echo htmlspecialchars($msg['email']); ?></a></td>
                            <td><div class="msg-contenu"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></div></td>
                            <td><?php echo htmlspecialchars($msg['date_envoi']); ?></td>
                            <td>
                                <?php if (!$msg['lu']): ?>
                                    <form action="index.php" method="POST" style="margin:0;">
                                        <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
                                        <input type="hidden" name="action" value="marquer_lu">
                                        <input type="hidden" name="type" value="contact">
                                        <input type="hidden" name="id" value="<?php echo $msg['id']; ?>">
                                        <button type="submit" class="btn-lu">✓ Lu</button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-muted">Aucun message reçu pour le moment.</p>
        <?php endif; ?>
    </div>

    <div class="section-box">
        <h2>Demandes de Projets (Devis)</h2>
        <?php if (!empty($demandesProjet)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Statut</th>
                        <th>Client</th>
                        <th>Email</th>
                        <th>Type de Projet</th>
                        <th>Description / Cahier des charges</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($demandesProjet as $demande): ?>
                        <tr>
                            <td>
                                <span class="statut <?php echo $demande['lu'] ? 'lu' : 'non-lu'; ?>">
                                    <?php echo $demande['lu'] ? 'Lu' : 'Nouveau'; ?>
                                </span>
                            </td>
                            <td><strong><?php echo htmlspecialchars($demande['nom']); ?></strong></td>
                            <td><a href="mailto:<?php echo htmlspecialchars($demande['email']); ?>"><?php echo htmlspecialchars($demande['email']); ?></a></td>
                            <td><code><?php echo htmlspecialchars($demande['type_projet']); ?></code></td>
                            <td><div class="msg-contenu"><?php echo nl2br(htmlspecialchars($demande['description'])); ?></div></td>
                            <td><?php echo htmlspecialchars($demande['date_demande']); ?></td>
                            <td>
                                <?php if (!$demande['lu']): ?>
                                    <form action="index.php" method="POST" style="margin:0;">
                                        <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
                                        <input type="hidden" name="action" value="marquer_lu">
                                        <input type="hidden" name="type" value="projet">
                                        <input type="hidden" name="id" value="<?php echo $demande['id']; ?>">
                                        <button type="submit" class="btn-lu">✓ Lu</button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-muted">Aucune demande de projet reçue pour le moment.</p>
        <?php endif; ?>
    </div>

</div>

</body>
</html>