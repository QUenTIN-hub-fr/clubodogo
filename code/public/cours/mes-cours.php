<?php
require_once __DIR__ . '/../../init.php';
exigerRole('coach');

$utilisateur = utilisateurConnecte();

$requete = $pdo->prepare(
    "SELECT cours.id, cours.titre, cours.date_cours, cours.heure_debut, cours.heure_fin,
            cours.capacite_max, type_cours.libelle_type
     FROM cours
     INNER JOIN type_cours ON cours.id_type_cours = type_cours.id
     WHERE cours.id_utilisateur = ? AND cours.date_cours >= CURDATE()
     ORDER BY cours.date_cours, cours.heure_debut"
);
$requete->execute([$utilisateur['id']]);
$mesCours = $requete->fetchAll();

$reqParticipants = $pdo->prepare(
    "SELECT chien.nom_chien, chien.date_naissance, race.libelle_race,
            utilisateur.prenom, utilisateur.nom
     FROM inscription
     INNER JOIN chien ON inscription.id_chien = chien.id
     INNER JOIN race ON chien.id_race = race.id
     INNER JOIN utilisateur ON chien.id_utilisateur = utilisateur.id
     WHERE inscription.id_cours = ? AND inscription.statut != 'annule'
     ORDER BY chien.nom_chien"
);

$titrePage = 'Mes cours';
require_once __DIR__ . '/../../header.php';
?>

<h1>Mes cours</h1>
<p>Les séances que j'anime et la liste des chiens inscrits.</p>

<?php if (empty($mesCours)): ?>
    <p class="vide">Aucun cours à venir ne vous est attribué.</p>
<?php endif; ?>

<?php foreach ($mesCours as $c): ?>
    <?php
    $reqParticipants->execute([$c['id']]);
    $participants = $reqParticipants->fetchAll();
    ?>
    <div class="carte" style="margin-top: 1.5rem;">
        <span class="badge"><?= proteger($c['libelle_type']) ?></span>
        <h2><?= proteger($c['titre']) ?></h2>
        <p><?= date('d/m/Y', strtotime($c['date_cours'])) ?>, de <?= substr($c['heure_debut'], 0, 5) ?> à <?= substr($c['heure_fin'], 0, 5) ?></p>
        <p><strong><?= count($participants) ?> chien(s) inscrit(s) sur <?= (int) $c['capacite_max'] ?> places</strong></p>

        <?php if (empty($participants)): ?>
            <p>Aucun chien inscrit pour le moment.</p>
        <?php else: ?>
            <table class="tableau">
                <thead>
                    <tr>
                        <th>Chien</th>
                        <th>Race</th>
                        <th>Âge le jour du cours</th>
                        <th>Propriétaire</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($participants as $p): ?>
                        <tr>
                            <td data-label="Chien"><?= proteger($p['nom_chien']) ?></td>
                            <td data-label="Race"><?= proteger($p['libelle_race']) ?></td>
                            <td data-label="Age"><?= ageEnMois($p['date_naissance'], $c['date_cours']) ?> mois</td>
                            <td data-label="Proprietaire"><?= proteger($p['prenom'] . ' ' . $p['nom']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
<?php endforeach; ?>

<?php require_once __DIR__ . '/../../footer.php'; ?>