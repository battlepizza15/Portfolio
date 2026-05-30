<?php
require_once 'config/connexion.php';
require_once 'fonctions.php';
enregistrerVisite($pdo);

$stmt = $pdo->query("SELECT * FROM projets ORDER BY date_creation DESC");
$projets = $stmt->fetchAll();

$mot_cle = nettoyer($_GET['q'] ?? '');
$resultats = [];
if ($mot_cle !== '') {
    foreach ($projets as $projet) {
        if (stripos($projet['titre'], $mot_cle) !== false ||
            stripos($projet['description'], $mot_cle) !== false) {
            $resultats[] = $projet;
        }
    }
} else {
    $resultats = $projets;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Projets | Moise Bienvenue</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css?v=1">
</head>
<body>

    <?php require 'composants/navigation.php'; ?>

    <main class="container">
        <section class="projets-header">
            <h1>Mes <span>Projets</span></h1>
            <p>Découvrez mes réalisations, des systèmes de gestion aux solutions informatiques.</p>
            <div class="search-container">
                <form action="projets.php" method="GET">
                    <input type="text" name="q" placeholder="Rechercher par mot-clé (ex: Linux, Arduino)..." value="<?= htmlspecialchars($mot_cle) ?>">
                    <button type="submit" class="btn primary">Rechercher</button>
                </form>
            </div>
        </section>

        <section class="projects-grid">
            <?php if (empty($resultats)) : ?>
                <p class="no-result">Aucun projet ne correspond à "<strong><?= htmlspecialchars($mot_cle) ?></strong>".</p>
            <?php else : ?>
                <?php foreach ($resultats as $projet) : ?>
                    <article class="project-card">
                        <div class="project-content">
                            <h3><?= htmlspecialchars($projet['titre']) ?></h3>
                            <p><?= htmlspecialchars($projet['description']) ?></p>
                            <div class="tags">
                                <?php foreach (explode(', ', $projet['technologies']) as $tech) : ?>
                                    <span><?= htmlspecialchars($tech) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </main>

    <?php require 'composants/pied-de-page.php'; ?>
</body>
</html>