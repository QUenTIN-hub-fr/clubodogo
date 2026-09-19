<?php
require_once __DIR__ . '/../init.php';

$titrePage = 'Accueil';
require_once __DIR__ . '/../header.php';
?>

<h1>Clubodogo</h1>

<?php if (estConnecte()): ?>
    <?php $utilisateur = utilisateurConnecte(); ?>
    <p>Bonjour <?= proteger($utilisateur['prenom']) ?>, vous etes connecte en tant que <?= proteger($utilisateur['role']) ?>.</p>
<?php else: ?>
    <p>Sociabilisation, dressage et parcours sportifs encadres par des coachs diplomes.</p>
    <p><a href="/cours/liste.php" class="bouton">Voir les cours</a></p>
<?php endif; ?>

<?php require_once __DIR__ . '/../footer.php'; ?>