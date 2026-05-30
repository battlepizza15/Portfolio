<?php
// 1. Démarrage de la session avant tout traitement (Section 3.2)
session_start();

// 2. Inclusion des fichiers de configuration (On remonte d'un dossier avec '../')
require_once '../config/connexion.php';
require_once '../fonctions.php';

// 3. Si l'administrateur est déjà connecté, redirection vers le dashboard (Section 5.1)
if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit();
}

$erreur = "";

// 4. Génération du jeton CSRF pour le formulaire (Section 3.2 & 5.1)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$token = $_SESSION['csrf_token'];

// 5. Traitement du formulaire lors de la soumission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Vérification stricte du jeton CSRF avec hash_equals()
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Attaque CSRF bloquée.");
    }

    // Récupération et nettoyage des données entrantes
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $mot_de_passe = trim($_POST['mot_de_passe']);

    if (!empty($email) && !empty($mot_de_passe)) {
        try {
            // Requête préparée pour chercher l'administrateur par son email (Section 3.2)
            $sql = "SELECT * FROM administrateurs WHERE email = :email LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':email' => $email]);
            $admin = $stmt->fetch();

            // Vérification du mot de passe haché avec password_verify() (Section 3.2)
            if ($admin && password_verify($mot_de_passe, $admin['mot_de_passe'])) {
                
                // Connexion réussie : Régénération immédiate de l'ID de session (Section 3.2 & 5.1)
                session_regenerate_id(true);

                // Stockage des informations autorisées en session (Pas le mot de passe !) (Section 3.2 & 5.1)
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_prenom'] = $admin['prenom'];
                $_SESSION['admin_nom'] = $admin['nom'];

                // Redirection vers le dashboard
                header('Location: dashboard.php');
                exit();
            } else {
                // Message d'erreur générique obligatoire (Section 5.1)
                $erreur = "Identifiants incorrects.";
            }
        } catch (PDOException $e) {
            error_log("Erreur connexion admin : " . $e->getMessage());
            $erreur = "Une erreur technique est survenue.";
        }
    } else {
        $erreur = "Veuillez remplir tous les champs.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion Administration</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f9; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-box { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        h2 { text-align: center; color: #333; margin-bottom: 20px; }
        .error { background: #ffe3e3; color: #c10000; padding: 10px; border-radius: 4px; margin-bottom: 15px; font-size: 14px; text-align: center; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; color: #666; }
        input[type="email"], input[type="password"] { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #007bff; border: none; color: white; border-radius: 4px; cursor: pointer; font-size: 16px; }
        button:hover { background: #0056b3; }
    </style>
</head>
<body>

<div class="login-box">
    <h2>Espace Admin</h2>
    
    <?php if (!empty($erreur)): ?>
        <div class="error"><?php echo htmlspecialchars($erreur); ?></div>
    <?php endif; ?>

    <form action="connexion.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">

        <div class="form-group">
            <label for="email">Adresse Email</label>
            <input type="email" id="email" name="email" required>
        </div>

        <div class="form-group">
            <label for="mot_de_passe">Mot de passe</label>
            <input type="password" id="mot_de_passe" name="mot_de_passe" required>
        </div>

        <button type="submit">Se connecter</button>
    </form>
</div>

</body>
</html>