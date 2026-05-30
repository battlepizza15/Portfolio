<?php
require_once 'config/connexion.php';
require_once 'fonctions.php';
enregistrerVisite($pdo);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moise Bienvenue | ETUDIANT EN GLAR</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css?v=1">
</head>
<body>

    <?php require 'composants/navigation.php'; ?>

    <section class="hero">
        <div class="hero-container">
            <div class="hero-image">
                <img src="image/moise-mb png.jpeg" alt="Photo de Moise Bienvenue">
            </div>
            <div class="hero-text">
                <h1><span>MATOBOUTSE MATOBOUTSE Moise Bienvenue</span></h1>
                <h3>ETUDIANT EN Génie Logiciel et Administration & Réseaux.</h3>
                <p class="location">Dakar, Dieupeul 3</p>
                <p class="description">
                    Actuellement en licence 2 en Génie Logiciel  Administrtion & Réseaux, je suis un jeune étudiant souhaitant se spécialisant dans le domaine de Réseaux et Télécom. J’oriente mes études dans ce domaine, guidé par mon désir d'apprendre et ma curiosité.
                    En tant que passionné, j’aime participer activement aux activités démandé, mais aussi créer des projets personnels, dans le but d'augmenter en compétences.
                </p> 
                
                <div class="cta-buttons">
                    <a href="projets.php" class="btn primary">Voir mes projets</a>
                    <a href="contact.php" class="btn secondary">Me contacter</a>
                </div> 
            </div>
        </div>
    </section>

    <?php require 'composants/pied-de-page.php'; ?>
    
</body>
</html>