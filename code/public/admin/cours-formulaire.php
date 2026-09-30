<?php
require_once __DIR__ . '/../../init.php';
exigerRole('responsable');

$id = (int) ($_GET['id'] ?? 0);
$modification = $id > 0;

$types = $pdo->query('SELECT id, libelle_type FROM type_cours ORDER BY libelle_type')->fetchAll();
$coachs = $pdo->query("SELECT id, prenom, nom FROM utilisateur WHERE role = 'coach' ORDER BY nom, prenom")->fetchAll();

$cours = [
    'titre' => '',
    'description' => '',
    'capacite_max' => 8,
    'age_min_mois' => 2,
    'age_max_mois' => '',
    'date_cours' => '',
    'heure_debut' => '',
    'heure_fin' => '',
    'id_utilisateur' => 0,
    'id_type_cours' => 0,
];

$erreurs = [];

if ($modification && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $requete = $pdo->prepare(
        'SELECT titre, description, capacite_max, age_min_mois, age_max_mois,
                date_cours, heure_debut, heure_fin, id_utilisateur, id_type_cours
         FROM cours WHERE id = ?'
    );
    $requete->execute([$id]);
    $trouve = $requete->fetch();

    // Un identifiant inexistant renvoie vers la liste plutôt que d'afficher un formulaire vide
    if (!$trouve) {
        header('Location: /admin/cours.php');
        exit;
    }

    $cours = $trouve;
    $cours['heure_debut'] = substr($cours['heure_debut'], 0, 5);
    $cours['heure_fin'] = substr($cours['heure_fin'], 0, 5);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verifierJetonCsrf();

    $cours['titre'] = trim($_POST['titre'] ?? '');
    $cours['description'] = trim($_POST['description'] ?? '');
    $cours['capacite_max'] = (int) ($_POST['capacite_max'] ?? 0);
    $cours['age_min_mois'] = (int) ($_POST['age_min_mois'] ?? 0);
    $cours['age_max_mois'] = trim($_POST['age_max_mois'] ?? '');
    $cours['date_cours'] = $_POST['date_cours'] ?? '';
    $cours['heure_debut'] = $_POST['heure_debut'] ?? '';
    $cours['heure_fin'] = $_POST['heure_fin'] ?? '';
    $cours['id_utilisateur'] = (int) ($_POST['id_utilisateur'] ?? 0);
    $cours['id_type_cours'] = (int) ($_POST['id_type_cours'] ?? 0);

    if ($cours['titre'] === '') {
        $erreurs[] = 'Le titre est obligatoire.';
    }
    if ($cours['description'] === '') {
        $erreurs[] = 'La description est obligatoire.';
    }
    if ($cours['date_cours'] === '') {
        $erreurs[] = 'La date est obligatoire.';
    } elseif (!$modification && $cours['date_cours'] < date('Y-m-d')) {
        $erreurs[] = 'Un nouveau cours ne peut pas être placé dans le passé.';
    }
    if ($cours['heure_debut'] === '' || $cours['heure_fin'] === '') {
        $erreurs[] = 'Les horaires sont obligatoires.';
    } elseif ($cours['heure_fin'] <= $cours['heure_debut']) {
        $erreurs[] = "L'heure de fin doit être postérieure à l'heure de début.";
    }
    if ($cours['capacite_max'] < 1 || $cours['capacite_max'] > 50) {
        $erreurs[] = 'Le nombre de places doit être compris entre 1 et 50.';
    }
    if ($cours['age_max_mois'] !== '' && (int) $cours['age_max_mois'] <= $cours['age_min_mois']) {
        $erreurs[] = "L'âge maximum doit être supérieur à l'âge minimum.";
    }

    // Le coach et le type sont vérifiés en base : le menu déroulant ne protège rien
    $verifCoach = $pdo->prepare("SELECT id FROM utilisateur WHERE id = ? AND role = 'coach'");
    $verifCoach->execute([$cours['id_utilisateur']]);
    if (!$verifCoach->fetch()) {
        $erreurs[] = 'Le coach sélectionné est introuvable.';
    }

    $verifType = $pdo->prepare('SELECT id FROM type_cours WHERE id = ?');
    $verifType->execute([$cours['id_type_cours']]);
    if (!$verifType->fetch()) {
        $erreurs[] = 'Le type de cours sélectionné est introuvable.';
    }

    // On ne peut pas réduire la capacité en dessous du nombre de chiens déjà inscrits
    if ($modification && empty($erreurs)) {
        $verifPlaces = $pdo->prepare("SELECT COUNT(*) AS total FROM inscription WHERE id_cours = ? AND statut != 'annule'");
        $verifPlaces->execute([$id]);
        $dejaInscrits = (int) $verifPlaces->fetch()['total'];

        if ($cours['capacite_max'] < $dejaInscrits) {
            $erreurs[] = "Le nombre de places ne peut pas être inférieur aux " . $dejaInscrits . " inscription(s) existante(s).";
        }
    }

    if (empty($erreurs)) {
        $ageMax = $cours['age_max_mois'] === '' ? null : (int) $cours['age_max_mois'];

        if ($modification) {
            $requete = $pdo->prepare(
                'UPDATE cours
                 SET titre = ?, description = ?, capacite_max = ?, age_min_mois = ?, age_max_mois = ?,
                     date_cours = ?, heure_debut = ?, heure_fin = ?, id_utilisateur = ?, id_type_cours = ?
                 WHERE id = ?'
            );
            $requete->execute([
                $cours['titre'], $cours['description'], $cours['capacite_max'],
                $cours['age_min_mois'], $ageMax, $cours['date_cours'],
                $cours['heure_debut'], $cours['heure_fin'],
                $cours['id_utilisateur'], $cours['id_type_cours'], $id
            ]);
        } else {
            $requete = $pdo->prepare(
                'INSERT INTO cours (titre, description, capacite_max, age_min_mois, age_max_mois,
                                    date_cours, heure_debut, heure_fin, id_utilisateur, id_type_cours)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $requete->execute([
                $cours['titre'], $cours['description'], $cours['capacite_max'],
                $cours['age_min_mois'], $ageMax, $cours['date_cours'],
                $cours['heure_debut'], $cours['heure_fin'],
                $cours['id_utilisateur'], $cours['id_type_cours']
            ]);
        }

        header('Location: /admin/cours.php');
        exit;
    }
}

$titrePage = $modification ? 'Modifier un cours' : 'Créer un cours';
require_once __DIR__ . '/../../header-admin.php';
?>

<h1><?= $modification ? 'Modifier un cours' : 'Créer un cours' ?></h1>

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

    <p>
        <label for="titre">Titre du cours</label>
        <input type="text" id="titre" name="titre" value="<?= proteger($cours['titre']) ?>" required>
    </p>
    <p>
        <label for="description">Description</label>
        <textarea id="description" name="description" rows="3" required><?= proteger($cours['description']) ?></textarea>
    </p>
    <p>
        <label for="id_type_cours">Type de cours</label>
        <select id="id_type_cours" name="id_type_cours" required>
            <option value="">Choisir un type</option>
            <?php foreach ($types as $type): ?>
                <option value="<?= (int) $type['id'] ?>" <?= ((int) $cours['id_type_cours'] === (int) $type['id']) ? 'selected' : '' ?>>
                    <?= proteger($type['libelle_type']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>
    <p>
        <label for="id_utilisateur">Coach</label>
        <select id="id_utilisateur" name="id_utilisateur" required>
            <option value="">Choisir un coach</option>
            <?php foreach ($coachs as $coach): ?>
                <option value="<?= (int) $coach['id'] ?>" <?= ((int) $cours['id_utilisateur'] === (int) $coach['id']) ? 'selected' : '' ?>>
                    <?= proteger($coach['prenom'] . ' ' . $coach['nom']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>
    <p>
        <label for="date_cours">Date</label>
        <input type="date" id="date_cours" name="date_cours" value="<?= proteger($cours['date_cours']) ?>" required>
    </p>
    <p>
        <label for="heure_debut">Heure de début</label>
        <input type="time" id="heure_debut" name="heure_debut" value="<?= proteger($cours['heure_debut']) ?>" required>
    </p>
    <p>
        <label for="heure_fin">Heure de fin</label>
        <input type="time" id="heure_fin" name="heure_fin" value="<?= proteger($cours['heure_fin']) ?>" required>
    </p>
    <p>
        <label for="capacite_max">Nombre de places</label>
        <input type="number" id="capacite_max" name="capacite_max" min="1" max="50" value="<?= proteger($cours['capacite_max']) ?>" required>
    </p>
    <p>
        <label for="age_min_mois">Âge minimum du chien, en mois</label>
        <input type="number" id="age_min_mois" name="age_min_mois" min="0" value="<?= proteger($cours['age_min_mois']) ?>" required>
    </p>
    <p>
        <label for="age_max_mois">Âge maximum du chien, en mois</label>
        <input type="number" id="age_max_mois" name="age_max_mois" min="0" value="<?= proteger($cours['age_max_mois'] ?? '') ?>">
        <small class="aide">Laisser vide s'il n'y a pas de limite haute.</small>
    </p>
    <div class="actions">
        <button type="submit" class="bouton">Enregistrer</button>
        <a href="/admin/cours.php">Annuler</a>
    </div>
</form>

<?php require_once __DIR__ . '/../../footer.php'; ?>