<?php
require_once __DIR__ . '/../../init.php';
exigerRole('proprietaire');

$utilisateur = utilisateurConnecte();

$requete = $pdo->prepare(
    'SELECT chien.id, chien.nom_chien, chien.date_naissance, chien.sexe,
            chien.num_puce, race.libelle_race
     FROM chien
     INNER JOIN race ON chien.id_race = race.id
     WHERE chien.id_utilisateur = ?
     ORDER BY chien.nom_chien'
);
$requete->execute([$utilisateur['id']]);
$chiens = $requete->fetchAll();

$titrePage = 'Mes chiens';
require_once __DIR__ . '/../../header.php';
?>

<div class="barre-titre">
    <h1>Mes chiens</h1>
    <a href="/chiens/formulaire.php" class="bouton bouton-orange">Ajouter un chien</a>
</div>

<?php if (empty($chiens)): ?>
    <p class="vide">Vous n'avez pas encore enregistré de chien. Ajoutez-en un pour pouvoir l'inscrire à un cours.</p>
<?php else: ?>
    <div class="grille">
        <?php foreach ($chiens as $chien): ?>
            <div class="carte">
                <h3><?= proteger($chien['nom_chien']) ?></h3>
                <p><?= proteger($chien['libelle_race']) ?> - <?= proteger($chien['sexe']) ?></p>
                <p>Né le <?= date('d/m/Y', strtotime($chien['date_naissance'])) ?></p>
                <p>Puce : <?= proteger($chien['num_puce']) ?></p>
                <div class="actions">
                    <a href="/chiens/formulaire.php?id=<?= (int) $chien['id'] ?>" class="bouton bouton-petit">Modifier</a>
                    <a href="/chiens/supprimer.php?id=<?= (int) $chien['id'] ?>" class="bouton bouton-petit bouton-rouge">Supprimer</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../../footer.php'; ?>