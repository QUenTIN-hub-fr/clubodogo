<?php
require_once __DIR__ . '/../../init.php';
exigerRole('responsable');

$succes = '';
$erreurs = [];

$idModification = (int) ($_GET['id'] ?? 0);
$libelleModification = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verifierJetonCsrf();

    $action = $_POST['action'] ?? '';

    if ($action === 'supprimer') {
        $idRace = (int) ($_POST['id_race'] ?? 0);

        // Une race encore attribuée à des chiens ne peut pas être supprimée
        $verif = $pdo->prepare('SELECT COUNT(*) AS total FROM chien WHERE id_race = ?');
        $verif->execute([$idRace]);

        if ((int) $verif->fetch()['total'] > 0) {
            $erreurs[] = "Cette race ne peut pas être supprimée : des chiens y sont rattachés.";
        } else {
            $pdo->prepare('DELETE FROM race WHERE id = ?')->execute([$idRace]);
            $succes = 'La race a bien été supprimée.';
        }
    } else {
        $libelle = trim($_POST['libelle_race'] ?? '');
        $idRace = (int) ($_POST['id_race'] ?? 0);

        if ($libelle === '') {
            $erreurs[] = 'Le libellé est obligatoire.';
        } elseif (mb_strlen($libelle) > 100) {
            $erreurs[] = 'Le libellé ne peut pas dépasser 100 caractères.';
        } else {
            // La même race ne doit pas exister deux fois, sauf pour la ligne en cours de modification
            $verif = $pdo->prepare('SELECT id FROM race WHERE libelle_race = ? AND id != ?');
            $verif->execute([$libelle, $idRace]);

            if ($verif->fetch()) {
                $erreurs[] = 'Cette race existe déjà.';
            } elseif ($idRace > 0) {
                $pdo->prepare('UPDATE race SET libelle_race = ? WHERE id = ?')->execute([$libelle, $idRace]);
                $succes = 'La race a bien été modifiée.';
            } else {
                $pdo->prepare('INSERT INTO race (libelle_race) VALUES (?)')->execute([$libelle]);
                $succes = 'La race a bien été ajoutée.';
            }
        }

        // En cas d'erreur sur une modification, le formulaire reste en mode modification
        if (!empty($erreurs)) {
            $idModification = $idRace;
            $libelleModification = $libelle;
        }
    }
}

if ($idModification > 0 && $libelleModification === '') {
    $requete = $pdo->prepare('SELECT libelle_race FROM race WHERE id = ?');
    $requete->execute([$idModification]);
    $trouve = $requete->fetch();

    if (!$trouve) {
        header('Location: /admin/races.php');
        exit;
    }

    $libelleModification = $trouve['libelle_race'];
}

$races = $pdo->query(
    "SELECT race.id, race.libelle_race,
            (SELECT COUNT(*) FROM chien WHERE chien.id_race = race.id) AS nb_chiens
     FROM race
     ORDER BY race.libelle_race"
)->fetchAll();

$titrePage = 'Gestion des races';
require_once __DIR__ . '/../../header-admin.php';
?>

<h1>Gestion des races</h1>

<?php if ($succes !== ''): ?>
    <div class="message message-succes"><?= proteger($succes) ?></div>
<?php endif; ?>

<?php if (!empty($erreurs)): ?>
    <div class="message message-erreur">
        <ul>
            <?php foreach ($erreurs as $erreur): ?>
                <li><?= proteger($erreur) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="post" class="formulaire">

    <?= champJetonCsrf() ?>

    <input type="hidden" name="id_race" value="<?= (int) $idModification ?>">
    <p>
        <label for="libelle_race"><?= $idModification > 0 ? 'Modifier la race' : 'Nouvelle race' ?></label>
        <input type="text" id="libelle_race" name="libelle_race" maxlength="100" value="<?= proteger($libelleModification) ?>" required>
    </p>
    <div class="actions">
        <button type="submit" class="bouton"><?= $idModification > 0 ? 'Enregistrer' : 'Ajouter' ?></button>
        <?php if ($idModification > 0): ?>
            <a href="/admin/races.php">Annuler</a>
        <?php endif; ?>
    </div>
</form>

<?php if (empty($races)): ?>
    <p class="vide">Aucune race enregistrée.</p>
<?php else: ?>
    <table class="tableau">
        <thead>
            <tr>
                <th>Race</th>
                <th>Chiens inscrits</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($races as $race): ?>
                <tr>
                    <td data-label="Race"><?= proteger($race['libelle_race']) ?></td>
                    <td data-label="Chiens inscrits"><?= (int) $race['nb_chiens'] ?></td>
                    <td data-label="Actions">
                        <div class="actions">
                            <a href="/admin/races.php?id=<?= (int) $race['id'] ?>" class="bouton bouton-petit">Modifier</a>
                            <form method="post" data-confirmation="Voulez-vous vraiment supprimer cette race ?">

                                <?= champJetonCsrf() ?>

                                <input type="hidden" name="action" value="supprimer">
                                <input type="hidden" name="id_race" value="<?= (int) $race['id'] ?>">
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