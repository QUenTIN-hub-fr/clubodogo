<?php
require_once __DIR__ . '/../../init.php';
exigerRole('proprietaire');

$utilisateur = utilisateurConnecte();
$erreurs = [];

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$modification = $id > 0;

$chien = [
    'nom_chien' => '',
    'date_naissance' => '',
    'sexe' => 'male',
    'num_puce' => '',
    'id_race' => ''
];

if ($modification) {
    $requete = $pdo->prepare('SELECT * FROM chien WHERE id = ? AND id_utilisateur = ?');
    $requete->execute([$id, $utilisateur['id']]);
    $trouve = $requete->fetch();

    if (!$trouve) {
        http_response_code(404);
        exit('Chien introuvable.');
    }
    $chien = $trouve;
}

$races = $pdo->query('SELECT id, libelle_race FROM race ORDER BY libelle_race')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $chien['nom_chien'] = trim($_POST['nom_chien'] ?? '');
    $chien['date_naissance'] = $_POST['date_naissance'] ?? '';
    $chien['sexe'] = $_POST['sexe'] ?? '';
    $chien['num_puce'] = trim($_POST['num_puce'] ?? '');
    $chien['id_race'] = (int) ($_POST['id_race'] ?? 0);

    if ($chien['nom_chien'] === '') {
        $erreurs[] = 'Le nom du chien est obligatoire.';
    }
    if ($chien['date_naissance'] === '' || strtotime($chien['date_naissance']) === false) {
        $erreurs[] = 'La date de naissance est obligatoire.';
    } elseif (strtotime($chien['date_naissance']) > time()) {
        $erreurs[] = 'La date de naissance ne peut pas être dans le futur.';
    }
    if (!in_array($chien['sexe'], ['male', 'femelle'])) {
        $erreurs[] = 'Le sexe doit être mâle ou femelle.';
    }
    if ($chien['num_puce'] === '') {
        $erreurs[] = 'Le numéro de puce est obligatoire.';
    }
    if ($chien['id_race'] <= 0) {
        $erreurs[] = 'La race est obligatoire.';
    }

    if (empty($erreurs)) {
        $verif = $pdo->prepare('SELECT id FROM chien WHERE num_puce = ? AND id != ?');
        $verif->execute([$chien['num_puce'], $id]);

        if ($verif->fetch()) {
            $erreurs[] = 'Un chien est déjà enregistré avec ce numéro de puce.';
        } else {
            if ($modification) {
                $requete = $pdo->prepare(
                    'UPDATE chien
                     SET nom_chien = ?, date_naissance = ?, sexe = ?, num_puce = ?, id_race = ?
                     WHERE id = ? AND id_utilisateur = ?'
                );
                $requete->execute([
                    $chien['nom_chien'], $chien['date_naissance'], $chien['sexe'],
                    $chien['num_puce'], $chien['id_race'], $id, $utilisateur['id']
                ]);
            } else {
                $requete = $pdo->prepare(
                    'INSERT INTO chien (nom_chien, date_naissance, sexe, num_puce, id_utilisateur, id_race)
                     VALUES (?, ?, ?, ?, ?, ?)'
                );
                $requete->execute([
                    $chien['nom_chien'], $chien['date_naissance'], $chien['sexe'],
                    $chien['num_puce'], $utilisateur['id'], $chien['id_race']
                ]);
            }

            header('Location: /chiens/liste.php');
            exit;
        }
    }
}

$titrePage = $modification ? 'Modifier un chien' : 'Ajouter un chien';
require_once __DIR__ . '/../../header.php';
?>

<h1><?= $modification ? 'Modifier un chien' : 'Ajouter un chien' ?></h1>

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
    <p>
        <label for="nom_chien">Nom du chien</label>
        <input type="text" id="nom_chien" name="nom_chien" value="<?= proteger($chien['nom_chien']) ?>" required>
    </p>
    <p>
        <label for="id_race">Race</label>
        <select id="id_race" name="id_race" required>
            <option value="">Choisir une race</option>
            <?php foreach ($races as $race): ?>
                <option value="<?= (int) $race['id'] ?>" <?= ((int) $chien['id_race'] === (int) $race['id']) ? 'selected' : '' ?>>
                    <?= proteger($race['libelle_race']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>
    <p>
        <label for="sexe">Sexe</label>
        <select id="sexe" name="sexe" required>
            <option value="male" <?= $chien['sexe'] === 'male' ? 'selected' : '' ?>>Mâle</option>
            <option value="femelle" <?= $chien['sexe'] === 'femelle' ? 'selected' : '' ?>>Femelle</option>
        </select>
    </p>
    <p>
        <label for="date_naissance">Date de naissance</label>
        <input type="date" id="date_naissance" name="date_naissance" value="<?= proteger($chien['date_naissance']) ?>" required>
    </p>
    <p>
        <label for="num_puce">Numéro de puce</label>
        <input type="text" id="num_puce" name="num_puce" value="<?= proteger($chien['num_puce']) ?>" required>
    </p>
    <div class="actions">
        <button type="submit" class="bouton">Enregistrer</button>
        <a href="/chiens/liste.php">Annuler</a>
    </div>
</form>

<?php require_once __DIR__ . '/../../footer.php'; ?>