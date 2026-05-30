<?php
session_start();
require_once '../../config/connexion.php';
require_once '../../fonctions.php';

// Sécurité : Vérification de connexion
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../connexion.php');
    exit();
}

$erreurs = [];
$succes = "";

// Gestion du jeton CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$token = $_SESSION['csrf_token'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. Vérification CSRF
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Attaque CSRF bloquée.");
    }

    // 2. Récupération et nettoyage des données textuelles
    $titre = nettoyer($_POST['titre'] ?? '');
    $description = nettoyer($_POST['description'] ?? '');
    $technologies = nettoyer($_POST['technologies'] ?? '');
    $lien = nettoyer($_POST['lien'] ?? '');
    $nom_image = null;

    // Validation des champs obligatoires
    if (!champ_requis($titre) || !champ_requis($description) || !champ_requis($technologies)) {
        $erreurs[] = "Le titre, la description et les technologies sont obligatoires.";
    }

    // 3. Gestion sécurisée de l'upload d'image (Exigence Section 5.3)
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        
        $fileTmpPath = $_FILES['image']['tmp_name'];
        $fileName = $_FILES['image']['name'];
        $fileSize = $_FILES['image']['size'];
        
        // Vérification de la taille (ex: max 2 Mo)
        $maxSize = 2 * 1024 * 1024;
        if ($fileSize > $maxSize) {
            $erreurs[] = "L'image est trop lourde (maximum 2 Mo).";
        }

        // Vérification de l'extension
        $detailsFichier = pathinfo($fileName);
        $extension = strtolower($detailsFichier['extension']);
        $extensionsAutorisees = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($extension, $extensionsAutorisees)) {
            $erreurs[] = "Format d'image non autorisé (uniquement JPG, JPEG, PNG, GIF, WEBP).";
        }

        // Si pas d'erreur sur l'image, on la renomme de manière unique
        if (empty($erreurs)) {
            $nom_image = md5(time() . $fileName) . '.' . $extension;
            $dossierDestination = '../../images/projets/';
            
            // Déplacement du fichier temporaire vers le dossier final
            if (!move_uploaded_uploaded_file($fileTmpPath, $dossierDestination . $nom_image)) {
                $erreurs[] = "Erreur lors du déplacement de l'image sur le serveur.";
                $nom_image = null;
            }
        }
    }

    // 4. Insertion dans la base de données si aucune erreur
    if (empty($erreurs)) {
        try {
            $sql = "INSERT INTO projets (titre, description, technologies, image, lien) 
                    VALUES (:titre, :description, :technologies, :image, :lien)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':titre'        => $titre,
                ':description'  => $description,
                ':technologies' => $technologies,
                ':image'        => $nom_image,
                ':lien'         => $lien
            ]);

            $succes = "Le projet a été ajouté avec succès !";
            
            // Redirection vers la liste après 1.5 seconde
            header("Refresh: 1.5; url=index.php");
        } catch (PDOException $e) {
            error_log("Erreur ajout projet : " . $e->getMessage());
            $erreurs[] = "Une erreur technique est survenue lors de l'enregistrement.";
        }
    }
}

// Correction d'une petite erreur de frappe native PHP (move_uploaded_file)
function move_uploaded_uploaded_file($tmp, $dest) {
    return move_uploaded_file($tmp, $dest);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un Projet - Admin</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; margin: 0; background: #f4f6f9; color: #333; }
        .navbar { background: #2c3e50; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: #ecf0f1; text-decoration: none; }
        .btn-retour { background: #7f8c8d; color: white; padding: 8px 15px; border-radius: 4px; text-decoration: none; }
        .container { padding: 30px; max-width: 700px; margin: 0 auto; }
        .form-box { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #34495e; }
        input[type="text"], input[type="url"], textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; font-family: inherit; }
        textarea { height: 120px; resize: vertical; }
        .btn-submit { background: #2ecc71; color: white; border: none; padding: 12px 20px; border-radius: 4px; cursor: pointer; font-size: 16px; font-weight: bold; width: 100%; }
        .btn-submit:hover { background: #27ae60; }
        .alert { padding: 12px; border-radius: 4px; margin-bottom: 20px; font-size: 14px; }
        .alert-danger { background: #ffe3e3; color: #c10000; }
        .alert-success { background: #d4edda; color: #155724; }
    </style>
</head>
<body>

<div class="navbar">
    <h1><a href="../dashboard.php" style="color:white;">Zone Admin</a> &raquo; Ajouter un projet</h1>
    <a href="index.php" class="btn-retour">Retour à la liste</a>
</div>

<div class="container">
    <div class="form-box">
        <h2>Créer un nouveau projet</h2>

        <?php if (!empty($erreurs)): ?>
            <div class="alert alert-danger">
                <?php foreach ($erreurs as $err) echo htmlspecialchars($err) . "<br>"; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($succes)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($succes); ?></div>
        <?php endif; ?>

        <form action="ajouter.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">

            <div class="form-group">
                <label for="titre">Titre du projet *</label>
                <input type="text" id="titre" name="titre" required placeholder="Ex: Création d'un site E-commerce">
            </div>

            <div class="form-group">
                <label for="technologies">Technologies utilisées *</label>
                <input type="text" id="technologies" name="technologies" required placeholder="Ex: PHP, MySQL, Bootstrap">
            </div>

            <div class="form-group">
                <label for="lien">Lien du projet (GitHub ou site en ligne)</label>
                <input type="url" id="lien" name="lien" placeholder="https://github.com/...">
            </div>

            <div class="form-group">
                <label for="image">Image ou Capture d'écran</label>
                <input type="file" id="image" name="image" accept="image/*">
            </div>

            <div class="form-group">
                <label for="description">Description détaillée *</label>
                <textarea id="description" name="description" required placeholder="Expliquez en quelques lignes l'objectif du projet..."></textarea>
            </div>

            <button type="submit" class="btn-submit">Enregistrer le projet</button>
        </form>
    </div>
</div>

</body>
</html>