<?php
require_once __DIR__ . '/../init.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clubodogo</title>
</head>
<body>
    <h1>Clubodogo</h1>

    <?php if (estConnecte()): ?>
        <?php $utilisateur = utilisateurConnecte(); ?>
        <p>Bonjour <?= proteger($utilisateur['prenom']) ?> <?= proteger($utilisateur['nom']) ?>.</p>
        <p>Vous etes connecte en tant que : <?= proteger($utilisateur['role']) ?></p>
        <p><a href="compte/deconnexion.php">Se deconnecter</a></p>
    <?php else: ?>
        <p>Bienvenue sur le site du club canin.</p>
        <p>
            <a href="compte/connexion.php">Se connecter</a> ou
            <a href="compte/inscription.php">creer un compte</a>
        </p>
    <?php endif; ?>
</body>
</html>