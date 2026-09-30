<?php
require_once __DIR__ . '/../../init.php';
exigerRole('responsable');

$nbMembres = (int) $pdo->query('SELECT COUNT(*) FROM utilisateur')->fetchColumn();
$nbCoachs = (int) $pdo->query("SELECT COUNT(*) FROM utilisateur WHERE role = 'coach'")->fetchColumn();
$nbChiens = (int) $pdo->query('SELECT COUNT(*) FROM chien')->fetchColumn();
$nbCours = (int) $pdo->query('SELECT COUNT(*) FROM cours')->fetchColumn();

// Seuls les cours à venir intéressent le responsable au quotidien
$nbCoursAVenir = (int) $pdo->query('SELECT COUNT(*) FROM cours WHERE date_cours >= CURDATE()')->fetchColumn();
$nbInscriptions = (int) $pdo->query('SELECT COUNT(*) FROM inscription')->fetchColumn();

$prochainsCours = $pdo->query(
    "SELECT cours.id, cours.titre, cours.date_cours, cours.heure_debut, cours.capacite_max,
            type_cours.libelle_type,
            utilisateur.prenom, utilisateur.nom,
            (SELECT COUNT(*) FROM inscription
             WHERE inscription.id_cours = cours.id AND inscription.statut != 'annule') AS nb_inscrits
     FROM cours
     JOIN type_cours ON type_cours.id = cours.id_type_cours
     JOIN utilisateur ON utilisateur.id = cours.id_utilisateur
     WHERE cours.date_cours >= CURDATE()
     ORDER BY cours.date_cours, cours.heure_debut
     LIMIT 5"
)->fetchAll();

$titrePage = 'Tableau de bord';
require_once __DIR__ . '/../../header-admin.php';
?>

<h1>Tableau de bord</h1>

<div class="chiffres">
    <div class="chiffre">
        <span class="chiffre-valeur"><?= $nbMembres ?></span>
        <span class="chiffre-libelle">membres inscrits</span>
    </div>
    <div class="chiffre">
        <span class="chiffre-valeur"><?= $nbCoachs ?></span>
        <span class="chiffre-libelle">coachs</span>
    </div>
    <div class="chiffre">
        <span class="chiffre-valeur"><?= $nbChiens ?></span>
        <span class="chiffre-libelle">chiens enregistrés</span>
    </div>
    <div class="chiffre">
        <span class="chiffre-valeur"><?= $nbCoursAVenir ?></span>
        <span class="chiffre-libelle">cours à venir</span>
    </div>
    <div class="chiffre">
        <span class="chiffre-valeur"><?= $nbCours ?></span>
        <span class="chiffre-libelle">cours au total</span>
    </div>
    <div class="chiffre">
        <span class="chiffre-valeur"><?= $nbInscriptions ?></span>
        <span class="chiffre-libelle">inscriptions</span>
    </div>
</div>

<h2>Les cinq prochains cours</h2>

<?php if (empty($prochainsCours)): ?>
    <p class="vide">Aucun cours à venir n'est programmé.</p>
<?php else: ?>
    <table class="tableau">
        <thead>
            <tr>
                <th>Date</th>
                <th>Cours</th>
                <th>Coach</th>
                <th>Places</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($prochainsCours as $cours): ?>
                <tr>
                    <td data-label="Date">
                        <?= date('d/m/Y', strtotime($cours['date_cours'])) ?>
                        à <?= substr($cours['heure_debut'], 0, 5) ?>
                    </td>
                    <td data-label="Cours"><?= proteger($cours['titre']) ?></td>
                    <td data-label="Coach"><?= proteger($cours['prenom'] . ' ' . $cours['nom']) ?></td>
                    <td data-label="Places">
                        <?= (int) $cours['nb_inscrits'] ?> / <?= (int) $cours['capacite_max'] ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require_once __DIR__ . '/../../footer.php'; ?>