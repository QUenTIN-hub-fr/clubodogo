<?php
require_once __DIR__ . '/../init.php';

$prochainsCours = $pdo->query(
    "SELECT cours.id, cours.titre, cours.date_cours, cours.heure_debut, cours.heure_fin,
            cours.capacite_max, type_cours.libelle_type,
            (SELECT COUNT(*) FROM inscription
             WHERE inscription.id_cours = cours.id AND inscription.statut != 'annule') AS nb_inscrits
     FROM cours
     INNER JOIN type_cours ON cours.id_type_cours = type_cours.id
     WHERE cours.date_cours >= CURDATE()
     ORDER BY cours.date_cours, cours.heure_debut
     LIMIT 3"
)->fetchAll();

$titrePage = 'Accueil';
require_once __DIR__ . '/../header.php';
?>

<section class="hero">
    <h1>Un club canin pour progresser avec votre chien</h1>
    <p>Sociabilisation, dressage et parcours sportifs encadres par des coachs diplomes. Inscrivez votre chien en quelques clics.</p>

    <?php if (estConnecte()): ?>
        <p class="salutation">Bonjour <?= proteger(utilisateurConnecte()['prenom']) ?>, ravi de vous revoir.</p>
    <?php endif; ?>

    <div class="actions">
        <a href="/cours/liste.php" class="bouton">Voir les cours</a>
        <?php if (!estConnecte()): ?>
            <a href="/compte/inscription.php" class="bouton bouton-orange">Creer un compte</a>
        <?php elseif (aLeRole('proprietaire')): ?>
            <a href="/chiens/liste.php" class="bouton bouton-orange">Mes chiens</a>
        <?php endif; ?>
    </div>
</section>

<h2 class="section-titre">Nos prochains cours</h2>

<?php if (empty($prochainsCours)): ?>
    <p class="vide">Aucun cours n'est programme pour le moment.</p>
<?php else: ?>
    <div class="grille">
        <?php foreach ($prochainsCours as $c): ?>
            <?php $placesRestantes = $c['capacite_max'] - $c['nb_inscrits']; ?>
            <div class="carte">
                <span class="badge"><?= proteger($c['libelle_type']) ?></span>
                <h3><?= proteger($c['titre']) ?></h3>
                <p><?= date('d/m/Y', strtotime($c['date_cours'])) ?>, de <?= substr($c['heure_debut'], 0, 5) ?> a <?= substr($c['heure_fin'], 0, 5) ?></p>
                <p>
                    <?php if ($placesRestantes > 0): ?>
                        <?= $placesRestantes ?> place(s) restante(s) sur <?= (int) $c['capacite_max'] ?>
                    <?php else: ?>
                        <strong>Complet</strong>
                    <?php endif; ?>
                </p>
                <div class="actions">
                    <a href="/cours/detail.php?id=<?= (int) $c['id'] ?>" class="bouton bouton-petit">Voir le cours</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <p style="margin-top: 1.5rem;"><a href="/cours/liste.php">Voir tous les cours</a></p>
<?php endif; ?>

<?php require_once __DIR__ . '/../footer.php'; ?>