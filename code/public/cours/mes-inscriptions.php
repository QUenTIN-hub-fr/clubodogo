<?php
require_once __DIR__ . '/../../init.php';
exigerRole('proprietaire');

$utilisateur = utilisateurConnecte();
$succes = '';
$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idInscription = (int) ($_POST['id_inscription'] ?? 0);

    // L'inscription doit concerner un chien du proprietaire et un cours a venir
    $verif = $pdo->prepare(
        "SELECT inscription.id
         FROM inscription
         INNER JOIN chien ON inscription.id_chien = chien.id
         INNER JOIN cours ON inscription.id_cours = cours.id
         WHERE inscription.id = ?
           AND chien.id_utilisateur = ?
           AND cours.date_cours >= CURDATE()
           AND inscription.statut != 'annule'"
    );
    $verif->execute([$idInscription, $utilisateur['id']]);

    if ($verif->fetch()) {
        $maj = $pdo->prepare("UPDATE inscription SET statut = 'annule' WHERE id = ?");
        $maj->execute([$idInscription]);
        $succes = 'L\'inscription a bien ete annulee.';
    } else {
        $erreur = 'Cette inscription ne peut pas etre annulee.';
    }
}

$requete = $pdo->prepare(
    "SELECT inscription.id, inscription.statut, cours.id AS id_cours, cours.titre,
            cours.date_cours, cours.heure_debut, chien.nom_chien
     FROM inscription
     INNER JOIN chien ON inscription.id_chien = chien.id
     INNER JOIN cours ON inscription.id_cours = cours.id
     WHERE chien.id_utilisateur = ?
     ORDER BY cours.date_cours, cours.heure_debut"
);
$requete->execute([$utilisateur['id']]);
$inscriptions = $requete->fetchAll();

$libellesStatut = [
    'en_attente' => 'En attente',
    'confirme' => 'Confirme',
    'annule' => 'Annule'
];

$titrePage = 'Mes inscriptions';
require_once __DIR__ . '/../../header.php';
?>

<h1>Mes inscriptions</h1>

<?php if ($succes !== ''): ?>
    <div class="message message-succes"><?= proteger($succes) ?></div>
<?php endif; ?>

<?php if ($erreur !== ''): ?>
    <div class="message message-erreur"><?= proteger($erreur) ?></div>
<?php endif; ?>

<?php if (empty($inscriptions)): ?>
    <p class="vide">Aucune inscription pour le moment. <a href="/cours/liste.php">Voir les cours</a></p>
<?php else: ?>
    <table class="tableau">
        <thead>
            <tr>
                <th>Cours</th>
                <th>Chien</th>
                <th>Date</th>
                <th>Statut</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($inscriptions as $i): ?>
                <tr>
                    <td data-label="Cours">
                        <a href="/cours/detail.php?id=<?= (int) $i['id_cours'] ?>"><?= proteger($i['titre']) ?></a>
                    </td>
                    <td data-label="Chien"><?= proteger($i['nom_chien']) ?></td>
                    <td data-label="Date"><?= date('d/m/Y', strtotime($i['date_cours'])) ?> a <?= substr($i['heure_debut'], 0, 5) ?></td>
                    <td data-label="Statut">
                        <span class="statut statut-<?= proteger($i['statut']) ?>"><?= $libellesStatut[$i['statut']] ?></span>
                    </td>
                    <td data-label="Action">
                        <?php if ($i['statut'] !== 'annule' && $i['date_cours'] >= date('Y-m-d')): ?>
                            <form method="post" data-confirmation="Voulez-vous vraiment annuler cette inscription ?">
                                <input type="hidden" name="id_inscription" value="<?= (int) $i['id'] ?>">
                                <button type="submit" class="bouton bouton-petit bouton-rouge">Annuler</button>
                            </form>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require_once __DIR__ . '/../../footer.php'; ?>