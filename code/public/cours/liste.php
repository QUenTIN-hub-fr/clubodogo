<?php
require_once __DIR__ . '/../../init.php';

$cours = $pdo->query(
    "SELECT cours.id, cours.titre, cours.date_cours, cours.heure_debut, cours.heure_fin,
            cours.capacite_max, cours.age_min_mois, cours.age_max_mois,
            type_cours.libelle_type,
            (SELECT COUNT(*) FROM inscription
             WHERE inscription.id_cours = cours.id AND inscription.statut != 'annule') AS nb_inscrits
     FROM cours
     INNER JOIN type_cours ON cours.id_type_cours = type_cours.id
     WHERE cours.date_cours >= CURDATE()
     ORDER BY cours.date_cours, cours.heure_debut"
)->fetchAll();

$titrePage = 'Nos cours';
require_once __DIR__ . '/../../header.php';
?>

<h1>Nos cours</h1>

<?php if (empty($cours)): ?>
    <p class="vide">Aucun cours n'est programmé pour le moment.</p>
<?php else: ?>
    <div class="grille">
        <?php foreach ($cours as $c): ?>
            <?php $placesRestantes = $c['capacite_max'] - $c['nb_inscrits']; ?>
            <div class="carte">
                <span class="badge"><?= proteger($c['libelle_type']) ?></span>
                <h3><?= proteger($c['titre']) ?></h3>
                <p><?= date('d/m/Y', strtotime($c['date_cours'])) ?>, de <?= substr($c['heure_debut'], 0, 5) ?> à <?= substr($c['heure_fin'], 0, 5) ?></p>
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
<?php endif; ?>

<?php require_once __DIR__ . '/../../footer.php'; ?>