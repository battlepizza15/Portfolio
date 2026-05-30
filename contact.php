<?php
require_once 'config/connexion.php';
require_once 'fonctions.php';
enregistrerVisite($pdo);
?>

<?php
require_once 'fonctions.php';
$erreurs  = [];
$message_succes = ""; 
$nom      = '';
$email    = '';
$message  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    
    // FORMULAIRE 1 : MESSAGE
    if (isset($_POST['submit_message'])) {
        $nom     = nettoyer($_POST['nom']     ?? '');
        $email   = nettoyer($_POST['email']   ?? '');
        $message = nettoyer($_POST['message'] ?? '');

        if (!champ_requis($nom))     $erreurs[] = 'Le nom est obligatoire.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $erreurs[] = 'L\'adresse e-mail est invalide.';
        if (!champ_requis($message)) $erreurs[] = 'Le message ne peut pas être vide.';
        
        if (empty($erreurs)) {
            try {
                // AJOUT : Insertion réelle dans la table messages_contact
                $sql = "INSERT INTO messages_contact (nom, email, message, lu, date_envoi) VALUES (:nom, :email, :message, 0, NOW())";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':nom'     => $nom,
                    ':email'   => $email,
                    ':message' => $message
                ]);
                
                $message_succes = "✅ Merci ! Votre message a bien été envoyé.";
                $nom = $email = $message = "";
            } catch (PDOException $e) {
                $erreurs[] = "Erreur lors de l'envoi du message : " . $e->getMessage();
            }
        }
    }

    // FORMULAIRE 2 : PROJET
    if (isset($_POST['submit_projet'])) {
        $client_nom  = nettoyer($_POST['client_nom'] ?? '');
        $type_projet = nettoyer($_POST['type_projet'] ?? 'web');
        $besoin      = nettoyer($_POST['besoin']     ?? '');
        
        if (!champ_requis($client_nom)) $erreurs[] = 'Le nom/entreprise est obligatoire.';
        if (!champ_requis($besoin))     $erreurs[] = 'La description du besoin est obligatoire.';

        if (empty($erreurs)) {
            try {
                // AJOUT : Insertion réelle dans la table demandes_projet
                $sql = "INSERT INTO demandes_projet (nom, email, type_projet, description, lu, date_demande) VALUES (:nom, :email, :type_projet, :description, 0, NOW())";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':nom'         => $client_nom,
                    ':email'       => '', // Laissé vide car ton formulaire n'a pas de champ email pour le projet
                    ':type_projet' => $type_projet,
                    ':description' => $besoin
                ]);

                $message_succes = "🚀 Votre demande de projet a été validée avec succès !";
            } catch (PDOException $e) {
                $erreurs[] = "Erreur lors de la validation du projet : " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact | Moise Bienvenue</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css?v=1">
</head>
<body>

    <?php require 'composants/navigation.php'; ?>

    <main class="container">
        <section class="contact-header">
            <h1>Me <span>Contacter</span></h1>
            <p>Un projet en tête ou une question ? N'hésitez pas à m'écrire.</p>
        </section>
        <div class="forms-wrapper">
    <section class="contact-form-section">
        <h2>Envoyer un message</h2>
        
        <?php if (isset($_POST['submit_message']) && $message_succes) : ?>
            <p style="color: #2ecc71; font-weight: bold; background: rgba(46, 204, 113, 0.1); padding: 10px; border-radius: 5px;"><?= $message_succes ?></p>
        <?php endif; ?>

        <?php if (isset($_POST['submit_message']) && !empty($erreurs)) : ?>
            <ul style="color: #ff4d4d; border: 1px solid #ff4d4d; padding: 10px; border-radius: 5px; list-style: none;">
                <?php foreach ($erreurs as $erreur) : ?>
                    <li>⚠️ <?= $erreur ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <form action="contact.php" method="POST">
            <div class="form-group">
                <label for="name">Nom complet</label>
                <input type="text" id="name" name="nom" value="<?= htmlspecialchars($nom) ?>">
            </div>
            <div class="form-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>">
            </div>
            <div class="form-group">
                <label for="message">Message</label>
                <textarea id="message" name="message" rows="5"><?= htmlspecialchars($message) ?></textarea>
            </div>
            <button type="submit" name="submit_message" class="btn primary">Envoyer</button>
        </form>
    </section>

    <section class="project-request-section">
        <h2>Demande de projet</h2>
        
        <?php if (isset($_POST['submit_projet']) && $message_succes) : ?>
            <p style="color: #2ecc71; font-weight: bold; background: rgba(46, 204, 113, 0.1); padding: 10px; border-radius: 5px;"><?= $message_succes ?></p>
        <?php endif; ?>

        <?php if (isset($_POST['submit_projet']) && !empty($erreurs)) : ?>
            <ul style="color: #ff4d4d; border: 1px solid #ff4d4d; padding: 10px; border-radius: 5px; list-style: none;">
                <?php foreach ($erreurs as $erreur) : ?>
                    <li>⚠️ <?= $erreur ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <form action="contact.php" method="POST">
            <div class="form-group">
                <label for="client-name">Nom/Entreprise</label>
                <input type="text" id="client-name" name="client_nom">
            </div>
            <div class="form-group">
                <label for="project-type">Type de projet</label>
                <select id="project-type" name="type_projet">
                    <option value="web">Site Web / Application</option>
                    <option value="mobile">Application Mobile</option>
                    <option value="autre">Autre</option>
                </select>
            </div>
            <div class="form-group">
                <label for="description">Description du besoin</label>
                <textarea id="description" name="besoin" rows="5" placeholder="Décrivez votre projet..."></textarea>
            </div>
            <button type="submit" name="submit_projet" class="btn secondary">Soumettre la demande</button>
        </form>
    </section>
</div>

    </main>
    <?php require 'composants/pied-de-page.php'; ?>
</body>
</html>