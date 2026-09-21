<?php
require_once __DIR__ . '/../../init.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$requete = $pdo->prepare(
    "SELECT cours.*, type_cours.libelle_type,
            utilisateur.prenom AS coach_prenom, utilisateur.nom AS coach_nom,
            (SELECT COUNT(*) FROM inscription
             WHERE inscription.id_cours = cours.id AND inscription.statut != 'annule') AS nb_inscrits
     FROM cours
     INNER JOIN type_cours ON cours.id_type_cours = type_cours.id
     INNER JOIN utilisateur ON cours.id_utilisateur = utilisateur.id
     WHERE cours.id = ?"
);
$requete->execute([$id]);
$cours = $requete->fetch();

if (!$cours) {
    http_response_code(404);
    exit('Cours introuvable.');
}

$placesRestantes = $cours['capacite_max'] - $cours['nb_inscrits'];
$erreurs = [];
$succes = '';
$chiens = [];
$idChien = 0;

if (aLeRole('proprietaire')) {
    $utilisateur = utilisateurConnecte();
    $req = $pdo->prepare('SELECT id, nom_chien, date_naissance FROM chien WHERE id_utilisateur = ? ORDER BY nom_chien');
    $req->execute([$utilisateur['id']]);
    $chiens = $req->fetchAll();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    exigerRole('proprietaire');
    $utilisateur = utilisateurConnecte();
    $idChien = (int) ($_POST['id_chien'] ?? 0);
    $existante = false;

    // Regle 1 : le chien doit appartenir au proprietaire connecte
    $req = $pdo->prepare('SELECT id, nom_chien, date_naissance FROM chien WHERE id = ? AND id_utilisateur = ?');
    $req->execute([$idChien, $utilisateur['id']]);
    $chien = $req->fetch();

    if (!$chien) {
        $erreurs[] = 'Veuillez choisir un de vos chiens.';
    } else {
        // Regle 2 : le cours ne doit pas etre passe
        if ($cours['date_cours'] < date('Y-m-d')) {
            $erreurs[] = 'Ce cours est deja passe.';
        }

        // Regle 3 : il doit rester au moins une place
        if ($placesRestantes <= 0) {
            $erreurs[] = 'Ce cours est complet.';
        }

        // Regle 4 : l'age du chien le jour du cours doit etre dans la tranche
        $age = ageEnMois($chien['date_naissance'], $cours['date_cours']);
        $tropJeune = $age < $cours['age_min_mois'];
        $tropAge = $cours['age_max_mois'] !== null && $age > $cours['age_max_mois'];

        if ($tropJeune || $tropAge) {
            $erreurs[] = $chien['nom_chien'] . ' aura ' . $age . ' mois le jour du cours. '
                . 'Ce cours est reserve aux chiens ' . trancheAge($cours['age_min_mois'], $cours['age_max_mois']) . '.';
        }

        // Regle 5 : le chien ne doit pas deja etre inscrit
        $req = $pdo->prepare('SELECT id, statut FROM inscription WHERE id_cours = ? AND id_chien = ?');
        $req->execute([$cours['id'], $chien['id']]);
        $existante = $req->fetch();

        if ($existante && $existante['statut'] !== 'annule') {
            $erreurs[] = $chien['nom_chien'] . ' est deja inscrit a ce cours.';
        }
    }

    if (empty($erreurs)) {
        if ($existante) {
            $maj = $pdo->prepare("UPDATE inscription SET statut = 'confirme', date_inscription = CURDATE() WHERE id = ?");
            $maj->execute([$existante['id']]);
        } else {
            $ajout = $pdo->prepare(
                "INSERT INTO inscription (date_inscription, statut, id_cours, id_chien)
                 VALUES (CURDATE(), 'confirme', ?, ?)"
            );
            $ajout->execute([$cours['id'], $chien['id']]);
        }
        $succes = $chien['nom_chien'] . ' est bien inscrit a ce cours.';
        $placesRestantes--;
    }
}

$titrePage = $cours['titre'];
require_once __DIR__ . '/../../header.php';
?>

<p><a href="/cours/liste.php">Retour aux cours</a></p>

<div class="detail">
    <div class="infos">
        <span class="badge"><?= proteger($cours['libelle_type']) ?></span>
        <h1><?= proteger($cours['titre']) ?></h1>
        <p><?= proteger($cours['description']) ?></p>
        <p><strong>Date :</strong> <?= date('d/m/Y', strtotime($cours['date_cours'])) ?></p>
        <p><strong>Horaire :</strong> <?= substr($cours['heure_debut'], 0, 5) ?> a <?= substr($cours['heure_fin'], 0, 5) ?></p>
        <p><strong>Coach :</strong> <?= proteger($cours['coach_prenom'] . ' ' . $cours['coach_nom']) ?></p>
        <p><strong>Age requis :</strong> <?= trancheAge($cours['age_min_mois'], $cours['age_max_mois']) ?></p>
        <p><strong>Places :</strong> <?= max(0, $placesRestantes) ?> restante(s) sur <?= (int) $cours['capacite_max'] ?></p>
    </div>

    <div class="encart-inscription">
        <h2>Inscrire mon chien</h2>

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

        <?php if (!estConnecte()): ?>
            <p>Connectez-vous pour inscrire votre chien a ce cours.</p>
            <p><a href="/compte/connexion.php" class="bouton">Se connecter</a></p>
        <?php elseif (!aLeRole('proprietaire')): ?>
            <p>Seuls les proprietaires peuvent inscrire un chien.</p>
        <?php elseif (empty($chiens)): ?>
            <p>Vous devez d'abord enregistrer un chien.</p>
            <p><a href="/chiens/formulaire.php" class="bouton">Ajouter un chien</a></p>
        <?php else: ?>
            <form method="post">
                <label for="id_chien">Choisir le chien</label>
                <select id="id_chien" name="id_chien" required>
                    <?php foreach ($chiens as $c): ?>
                        <option value="<?= (int) $c['id'] ?>" <?= $idChien === (int) $c['id'] ? 'selected' : '' ?>>
                            <?= proteger($c['nom_chien']) ?> (<?= ageEnMois($c['date_naissance'], $cours['date_cours']) ?> mois le jour du cours)
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="bouton">Confirmer l'inscription</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../footer.php'; ?>