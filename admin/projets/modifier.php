<?php
session_start();
require_once '../../config/connexion.php';
require_once '../../fonctions.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../connexion.php');
    exit();
}

$erreurs = [];
$succes = "";

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$token = $_SESSION['csrf_token'];

// 1. Récupération du projet à modifier via l'ID dans l'URL
$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: index.php');
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT * FROM projets WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $projet = $stmt->fetch();

    if (!$projet) {
        header('Location: index.php');
        exit();
    }
} catch (PDOException $e) {
    die("Erreur de chargement du projet.");
}

// 2. Traitement de la mise à jour (Formulaire soumis)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Attaque CSRF bloquée.");
    }

    $titre = nettoyer($_POST['titre'] ?? '');
    $description = nettoyer($_POST['description'] ?? '');
    $technologies = nettoyer($_POST['technologies'] ?? '');
    $lien = nettoyer($_POST['lien'] ?? '');
    $nom_image = $projet['image']; // Par défaut on garde l'ancienne image

    if (!champ_requis($titre) || !champ_requis($description) || !champ_requis($technologies)) {
        $erreurs[] = "Le titre, la description et les technologies sont obligatoires.";
    }

    // Gestion du remplacement de l'image (optionnel)
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['image']['tmp_name'];
        $fileName = $_FILES['image']['name'];
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $extensionsAutorisees = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($extension, $extensionsAutorisees)) {
            $erreurs[] = "Format d'image non autorisé.";
        }

        if (empty($erreurs)) {
            // Supprimer l'ancienne image du serveur si elle existe
            if (!empty($projet['image'])) {
                $ancienneImage = '../../images/projets/' . $projet['image'];
                if (file_exists($ancienneImage)) {
                    unlink($ancienneImage);
                }
            }
            // Enregistrer la nouvelle image
            $nom_image = md5(time() . $fileName) . '.' . $extension;
            move_uploaded_file($fileTmpPath, '../../images/projets/' . $nom_image);
        }
    }

    // Mise à jour en BDD si aucune erreur
    if (empty($erreurs)) {
        try {
            $sql = "UPDATE projets SET titre = :titre, description = :description, technologies = :technologies, image = :image, lien = :lien WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':titre'        => $titre,
                ':description'  => $description,
                ':technologies' => $technologies,
                ':image'        => $nom_image,
                ':lien'         => $lien,
                ':id'           => $id
            ]);

            $succes = "Le projet a été mis à jour !";
            header("Refresh: 1.5; url=index.php");
        } catch (PDOException $e) {
            error_log("Erreur modification projet : " . $e->getMessage());
            $erreurs[] = "Une erreur technique est survenue.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier un Projet - Admin</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; margin: 0; background: #f4f6f9; color: #333; }
        .navbar { background: #2c3e50; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .btn-retour { background: #7f8c8d; color: white; padding: 8px 15px; border-radius: 4px; text-decoration: none; }
        .container { padding: 30px; max-width: 700px; margin: 0 auto; }
        .form-box { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #34495e; }
        input[type="text"], input[type="url"], textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        textarea { height: 120px; }
        .btn-submit { background: #3498db; color: white; border: none; padding: 12px 20px; border-radius: 4px; cursor: pointer; font-size: 16px; font-weight: bold; width: 100%; }
        .alert { padding: 12px; border-radius: 4px; margin-bottom: 20px; }
        .alert-danger { background: #ffe3e3; color: #c10000; }
        .alert-success { background: #d4edda; color: #155724; }
    </style>
</head>
<body>

<div class="navbar">
    <h1>Zone Admin &raquo; Modifier le projet</h1>
    <a href="index.php" class="btn-retour">Annuler</a>
</div>

<div class="container">
    <div class="form-box">
        <h2>Modifier : <?php echo htmlspecialchars($projet['titre']); ?></h2>

        <?php if (!empty($erreurs)): ?>
            <div class="alert alert-danger"><?php foreach ($erreurs as $err) echo htmlspecialchars($err) . "<br>"; ?></div>
        <?php endif; ?>

        <?php if (!empty($succes)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($succes); ?></div>
        <?php endif; ?>

        <form action="modifier.php?id=<?php echo $id; ?>" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">

            <div class="form-group">
                <label for="titre">Titre du projet *</label>
                <input type="text" id="titre" name="titre" value="<?php echo htmlspecialchars($projet['titre']); ?>" required>
            </div>

            <div class="form-group">
                <label for="technologies">Technologies utilisées *</label>
                <input type="text" id="technologies" name="technologies" value="<?php echo htmlspecialchars($projet['technologies']); ?>" required>
            </div>

            <div class="form-group">
                <label for="lien">Lien du projet</label>
                <input type="url" id="lien" name="lien" value="<?php echo htmlspecialchars($projet['lien']); ?>">
            </div>

            <div class="form-group">
                <label for="image">Changer l'image (Laissez vide pour conserver l'actuelle)</label>
                <input type="file" id="image" name="image" accept="image/*">
            </div>

            <div class="form-group">
                <label for="description">Description détaillée *</label>
                <textarea id="description" name="description" required><?php echo htmlspecialchars($projet['description']); ?></textarea>
            </div>

            <button type="submit" class="btn-submit">Mettre à jour le projet</button>
        </form>
    </div>
</div>

</body>
</html>