<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($titrePage) ? proteger($titrePage) . ' - Clubodogo' : 'Clubodogo' ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<header class="entete">
    <div class="entete-interieur">
        <a href="/index.php" class="logo">
            <img src="/assets/images/logo-blanc.png" alt="Clubodogo">
        </a>

        <button class="burger" id="burger" aria-label="Ouvrir le menu">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <nav class="navigation" id="navigation">
            <a href="/index.php">Accueil</a>
            <a href="/cours/liste.php">Cours</a>

            <?php if (estConnecte()): ?>
                <?php $connecte = utilisateurConnecte(); ?>

                <?php if (aLeRole('proprietaire')): ?>
                    <a href="/chiens/liste.php">Mes chiens</a>
                    <a href="/cours/mes-inscriptions.php">Mes inscriptions</a>
                <?php endif; ?>

                <?php if (aLeRole('coach')): ?>
                    <a href="/cours/mes-cours.php">Mes cours</a>
                <?php endif; ?>

                <?php if (aLeRole('responsable')): ?>
                    <a href="/admin/index.php">Administration</a>
                <?php endif; ?>

                <a href="/compte/profil.php" class="lien-compte"><?= proteger($connecte['prenom']) ?></a>
                <a href="/compte/deconnexion.php">Deconnexion</a>
            <?php else: ?>
                <a href="/compte/inscription.php">Creer un compte</a>
                <a href="/compte/connexion.php" class="bouton bouton-orange">Connexion</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main class="contenu">