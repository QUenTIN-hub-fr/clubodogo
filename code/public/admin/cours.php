<?php
require_once __DIR__ . '/../../init.php';
exigerRole('responsable');

$succes = '';
$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verifierJetonCsrf();

    $idCours = (int) ($_POST['id_cours'] ?? 0);

    $verif = $pdo->prepare("SELECT COUNT(*) AS total FROM inscription WHERE id_cours = ? AND statut != 'annule'");
    $verif->execute([$idCours]);

    if ((int) $verif->fetch()['total'] > 0) {
        $erreur = "Ce cours ne peut pas être supprimé : des chiens y sont inscrits.";
    } else {
        // Les deux suppressions forment un tout : soit les deux aboutissent, soit aucune
        try {
            $pdo->beginTransaction();

            $pdo->prepare('DELETE FROM inscription WHERE id_cours = ?')->execute([$idCours]);
            $pdo->prepare('DELETE FROM cours WHERE id = ?')->execute([$idCours]);

            $pdo->commit();

            $succes = 'Le cours a bien été supprimé.';

        } catch (PDOException $e) {
            $pdo->rollBack();
            $erreur = "La suppression a échoué, aucune donnée n'a été modifiée.";
        }
    }
}

$cours = $pdo->query(
    "SELECT cours.id, cours.titre, cours.date_cours, cours.heure_debut, cours.capacite_max,
            type_cours.libelle_type,
            utilisateur.prenom AS coach_prenom, utilisateur.nom AS coach_nom,
            (SELECT COUNT(*) FROM inscription
             WHERE inscription.id_cours = cours.id AND inscription.statut != 'annule') AS nb_inscrits
     FROM cours
     INNER JOIN type_cours ON cours.id_type_cours = type_cours.id
     INNER JOIN utilisateur ON cours.id_utilisateur = utilisateur.id
     ORDER BY cours.date_cours DESC, cours.heure_debut"
)->fetchAll();

$titrePage = 'Gestion des cours';
require_once __DIR__ . '/../../header-admin.php';
?>

<div class="barre-titre">
    <h1>Gestion des cours</h1>
    <a href="/admin/cours-formulaire.php" class="bouton bouton-orange">Créer un cours</a>
</div>

<?php if ($succes !== ''): ?>
    <div class="message message-succes"><?= proteger($succes) ?></div>
<?php endif; ?>

<?php if ($erreur !== ''): ?>
    <div class="message message-erreur"><?= proteger($erreur) ?></div>
<?php endif; ?>

<?php if (empty($cours)): ?>
    <p class="vide">Aucun cours enregistré.</p>
<?php else: ?>
    <table class="tableau">
        <thead>
            <tr>
                <th>Cours</th>
                <th>Type</th>
                <th>Date</th>
                <th>Coach</th>
                <th>Inscrits</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($cours as $c): ?>
                <tr>
                    <td data-label="Cours"><?= proteger($c['titre']) ?></td>
                    <td data-label="Type"><?= proteger($c['libelle_type']) ?></td>
                    <td data-label="Date"><?= date('d/m/Y', strtotime($c['date_cours'])) ?> à <?= substr($c['heure_debut'], 0, 5) ?></td>
                    <td data-label="Coach"><?= proteger($c['coach_prenom'] . ' ' . $c['coach_nom']) ?></td>
                    <td data-label="Inscrits"><?= (int) $c['nb_inscrits'] ?> / <?= (int) $c['capacite_max'] ?></td>
                    <td data-label="Actions">
                        <div class="actions">
                            <a href="/admin/cours-formulaire.php?id=<?= (int) $c['id'] ?>" class="bouton bouton-petit">Modifier</a>
                            <form method="post" data-confirmation="Voulez-vous vraiment supprimer ce cours ?">

                                <?= champJetonCsrf() ?>

                                <input type="hidden" name="id_cours" value="<?= (int) $c['id'] ?>">
                                <button type="submit" class="bouton bouton-petit bouton-rouge">Supprimer</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require_once __DIR__ . '/../../footer.php'; ?>