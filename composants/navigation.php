<header> 
   <nav class="navbar">
            <div class="logo">MB<span>.</span></div>
            <ul class="nav-links">
                <li><a href="index.php">Accueil</a></li>
                <li><a href="projets.php">Projets</a></li>
                <li><a href="parcours.php">Parcours</a></li>
                <li><a href="contact.php">Contact</a></li>
            </ul>
        </nav>
</header>

<?php
$page_courante = basename($_SERVER['PHP_SELF']);
?>

<nav>
    <a href="index.php"
       <?php if ($page_courante === 'index.php') echo 'class="actif"'; ?>>
       Accueil
    </a>
    <a href="projets.php"
       <?php if ($page_courante === 'projets.php') echo 'class="actif"'; ?>>
       Projets
    </a>
    <a href="contact.php"
       <?php if ($page_courante === 'contact.php') echo 'class="actif"'; ?>>
       Contact
    </a>
</nav>