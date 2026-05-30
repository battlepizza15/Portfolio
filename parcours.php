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
    <title>Mon Parcours | Moise Bienvenue</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css?v=1">
</head>
<body>

    <?php require 'composants/navigation.php'; ?>

    <main class="container">
        <section class="timeline-section">
            <h1>Mon <span>Expérience</span></h1>
            <div class="timeline">
                <div class="timeline-item">
                    <div class="date">2026 - Present</div>
                    <div class="content">
                        <h3>Licence 2</h3>
                        <p>Formation en téléinformatique.</p>
                    </div>
                </div> 

                <div class="timeline-item">
                    <div class="date">2023 - 2024</div>
                    <div class="content">
                        <h3>Secrétaire Général Auto-Ecole Mandji</h3>
                        <p>Gestion administrative et organisation des activités d’une auto-école.<hr>Tout en assurant le suivi des dossiers des élèves et la coordination des services.</p>
                    </div>
                </div>
                
                <div class="timeline-item">
                    <div class="date">2022 - 2023</div>
                    <div class="content">
                        <h3>Agent commercial Moov - Gabon Télécom</h3>
                        <p>Assistant et présentation des offres internet et appels.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="skills-section">
            <h2>Mes <span>Compétences</span> Techniques</h2>
            <div class="skills-grid">
                <div class="skill-card">Java</div>
                <div class="skill-card">JavaScript</div>
                <div class="skill-card">PHP</div>
                <div class="skill-card">Python</div>
                <div class="skill-card">Bases de données (SQL & NoSQL)</div>
                <div class="skill-card">Sécurité (cryptographie) </div>
                <div class="skill-card">Développement Web Statique</div>
                <div class="skill-card">Langage C</div>
            </div>
        </section>

        <section class="certifs-section">
            <h2>Certifications</h2>
            <ul class="certifs-list">
                <li>Permis B</li>
                <li>Baccalauréat</li>
                <li>Certificat d'Etude Primaire</li>
                <li>BEPC</li>
            </ul>
        </section>
    </main>

    <?php require 'composants/pied-de-page.php'; ?>

</body>
</html>