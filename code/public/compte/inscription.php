<?php
require_once __DIR__ . '/../../init.php';

$erreurs = [];
$succes = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $motDePasse = $_POST['mot_de_passe'] ?? '';
    $confirmation = $_POST['confirmation'] ?? '';

    if ($nom === '') {
        $erreurs[] = 'Le nom est obligatoire.';
    }
    if ($prenom === '') {
        $erreurs[] = 'Le prenom est obligatoire.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreurs[] = "L'adresse email n'est pas valide.";
    }
    if (strlen($motDePasse) < 8) {
        $erreurs[] = 'Le mot de passe doit contenir au moins 8 caracteres.';
    }
    if (!preg_match('/[A-Z]/', $motDePasse)) {
        $erreurs[] = 'Le mot de passe doit contenir au moins une majuscule.';
    }
    if (!preg_match('/[0-9]/', $motDePasse)) {
        $erreurs[] = 'Le mot de passe doit contenir au moins un chiffre.';
    }
    if ($motDePasse !== $confirmation) {
        $erreurs[] = 'Les deux mots de passe ne sont pas identiques.';
    }

    if (empty($erreurs)) {
        $requete = $pdo->prepare('SELECT id FROM utilisateur WHERE email = ?');
        $requete->execute([$email]);

        if ($requete->fetch()) {
            $erreurs[] = 'Un compte existe deja avec cette adresse email.';
        } else {
            $motDePasseHache = password_hash($motDePasse, PASSWORD_DEFAULT);

            $insertion = $pdo->prepare(
                'INSERT INTO utilisateur (nom, prenom, email, password, role)
                 VALUES (?, ?, ?, ?, ?)'
            );
            $insertion->execute([$nom, $prenom, $email, $motDePasseHache, 'proprietaire']);

            $succes = true;
        }
    }
}

$titrePage = 'Creer un compte';
require_once __DIR__ . '/../../header.php';
?>

<h1>Creer un compte</h1>

<?php if ($succes): ?>
    <div class="formulaire">
        <div class="message message-succes">Votre compte a bien ete cree. Vous pouvez maintenant vous connecter.</div>
        <a href="connexion.php" class="bouton">Se connecter</a>
    </div>
<?php else: ?>

    <form method="post" action="inscription.php" class="formulaire">

        <?php if (!empty($erreurs)): ?>
            <div class="message message-erreur">
                <ul>
                    <?php foreach ($erreurs as $erreur): ?>
                        <li><?= proteger($erreur) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <p>
            <label for="nom">Nom</label>
            <input type="text" id="nom" name="nom" value="<?= proteger($nom ?? '') ?>" required>
        </p>
        <p>
            <label for="prenom">Prenom</label>
            <input type="text" id="prenom" name="prenom" value="<?= proteger($prenom ?? '') ?>" required>
        </p>
        <p>
            <label for="email">Adresse email</label>
            <input type="email" id="email" name="email" value="<?= proteger($email ?? '') ?>" required>
        </p>
        <p>
            <label for="mot_de_passe">Mot de passe</label>
            <span class="champ-mdp">
                <input type="password" id="mot_de_passe" name="mot_de_passe" required>
                <button type="button" class="afficher-mdp" data-cible="mot_de_passe">Afficher</button>
            </span>
            <small class="aide">8 caracteres minimum, dont une majuscule et un chiffre.</small>
        </p>
        <p>
            <label for="confirmation">Confirmer le mot de passe</label>
            <span class="champ-mdp">
                <input type="password" id="confirmation" name="confirmation" required>
                <button type="button" class="afficher-mdp" data-cible="confirmation">Afficher</button>
            </span>
        </p>
        <div class="actions">
            <button type="submit" class="bouton">Creer mon compte</button>
            <a href="connexion.php">J'ai deja un compte</a>
        </div>
    </form>

<?php endif; ?>

<?php require_once __DIR__ . '/../../footer.php'; ?>