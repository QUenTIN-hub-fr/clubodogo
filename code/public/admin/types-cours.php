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
        $idType = (int) ($_POST['id_type'] ?? 0);

        // Un type de cours rattaché à des séances existantes ne peut pas être supprimé
        $verif = $pdo->prepare('SELECT COUNT(*) AS total FROM cours WHERE id_type_cours = ?');
        $verif->execute([$idType]);

        if ((int) $verif->fetch()['total'] > 0) {
            $erreurs[] = "Ce type de cours ne peut pas être supprimé : des cours y sont rattachés.";
        } else {
            $pdo->prepare('DELETE FROM type_cours WHERE id = ?')->execute([$idType]);
            $succes = 'Le type de cours a bien été supprimé.';
        }
    } else {
        $libelle = trim($_POST['libelle_type'] ?? '');
        $idType = (int) ($_POST['id_type'] ?? 0);

        if ($libelle === '') {
            $erreurs[] = 'Le libellé est obligatoire.';
        } elseif (mb_strlen($libelle) > 100) {
            $erreurs[] = 'Le libellé ne peut pas dépasser 100 caractères.';
        } else {
            // Le même libellé ne doit pas exister deux fois, sauf pour la ligne en cours de modification
            $verif = $pdo->prepare('SELECT id FROM type_cours WHERE libelle_type = ? AND id != ?');
            $verif->execute([$libelle, $idType]);

            if ($verif->fetch()) {
                $erreurs[] = 'Ce type de cours existe déjà.';
            } elseif ($idType > 0) {
                $pdo->prepare('UPDATE type_cours SET libelle_type = ? WHERE id = ?')->execute([$libelle, $idType]);
                $succes = 'Le type de cours a bien été modifié.';
            } else {
                $pdo->prepare('INSERT INTO type_cours (libelle_type) VALUES (?)')->execute([$libelle]);
                $succes = 'Le type de cours a bien été ajouté.';
            }
        }

        // En cas d'erreur sur une modification, le formulaire reste en mode modification
        if (!empty($erreurs)) {
            $idModification = $idType;
            $libelleModification = $libelle;
        }
    }
}

if ($idModification > 0 && $libelleModification === '') {
    $requete = $pdo->prepare('SELECT libelle_type FROM type_cours WHERE id = ?');
    $requete->execute([$idModification]);
    $trouve = $requete->fetch();

    if (!$trouve) {
        header('Location: /admin/types-cours.php');
        exit;
    }

    $libelleModification = $trouve['libelle_type'];
}

$types = $pdo->query(
    "SELECT type_cours.id, type_cours.libelle_type,
            (SELECT COUNT(*) FROM cours WHERE cours.id_type_cours = type_cours.id) AS nb_cours
     FROM type_cours
     ORDER BY type_cours.libelle_type"
)->fetchAll();

$titrePage = 'Gestion des types de cours';
require_once __DIR__ . '/../../header-admin.php';
?>

<h1>Gestion des types de cours</h1>

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

    <input type="hidden" name="id_type" value="<?= (int) $idModification ?>">
    <p>
        <label for="libelle_type"><?= $idModification > 0 ? 'Modifier le type de cours' : 'Nouveau type de cours' ?></label>
        <input type="text" id="libelle_type" name="libelle_type" maxlength="100" value="<?= proteger($libelleModification) ?>" required>
    </p>
    <div class="actions">
        <button type="submit" class="bouton"><?= $idModification > 0 ? 'Enregistrer' : 'Ajouter' ?></button>
        <?php if ($idModification > 0): ?>
            <a href="/admin/types-cours.php">Annuler</a>
        <?php endif; ?>
    </div>
</form>

<?php if (empty($types)): ?>
    <p class="vide">Aucun type de cours enregistré.</p>
<?php else: ?>
    <table class="tableau">
        <thead>
            <tr>
                <th>Type de cours</th>
                <th>Cours rattachés</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($types as $type): ?>
                <tr>
                    <td data-label="Type de cours"><?= proteger($type['libelle_type']) ?></td>
                    <td data-label="Cours rattachés"><?= (int) $type['nb_cours'] ?></td>
                    <td data-label="Actions">
                        <div class="actions">
                            <a href="/admin/types-cours.php?id=<?= (int) $type['id'] ?>" class="bouton bouton-petit">Modifier</a>
                            <form method="post" data-confirmation="Voulez-vous vraiment supprimer ce type de cours ?">

                                <?= champJetonCsrf() ?>

                                <input type="hidden" name="action" value="supprimer">
                                <input type="hidden" name="id_type" value="<?= (int) $type['id'] ?>">
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