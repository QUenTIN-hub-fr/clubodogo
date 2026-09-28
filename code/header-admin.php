<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($titrePage) ? proteger($titrePage) . ' - Administration Clubodogo' : 'Administration Clubodogo' ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<header class="entete">
    <div class="entete-interieur">
        <a href="/admin/index.php" class="logo">
            <img src="/assets/images/logo-blanc.png" alt="Clubodogo">
        </a>
        <span class="mention-admin">Administration</span>

        <button class="burger" id="burger" aria-label="Ouvrir le menu">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <nav class="navigation" id="navigation">
            <a href="/admin/index.php">Tableau de bord</a>
            <a href="/admin/cours.php">Cours</a>
            <a href="/admin/types-cours.php">Types de cours</a>
            <a href="/admin/races.php">Races</a>
            <a href="/admin/membres.php">Membres</a>
            <a href="/index.php">Voir le site</a>
            <a href="/compte/deconnexion.php">Deconnexion</a>
        </nav>
    </div>
</header>

<main class="contenu">