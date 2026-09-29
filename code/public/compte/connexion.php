<?php
require_once __DIR__ . '/../../init.php';

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $motDePasse = $_POST['mot_de_passe'] ?? '';

    if ($email === '' || $motDePasse === '') {
        $erreur = 'Veuillez remplir les deux champs.';
    } else {
        $requete = $pdo->prepare('SELECT id, nom, prenom, email, password, role FROM utilisateur WHERE email = ?');
        $requete->execute([$email]);
        $utilisateur = $requete->fetch();

        if ($utilisateur && password_verify($motDePasse, $utilisateur['password'])) {

            session_regenerate_id(true);

            unset($utilisateur['password']);
            $_SESSION['utilisateur_id'] = $utilisateur['id'];
            $_SESSION['utilisateur'] = $utilisateur;

            header('Location: /index.php');
            exit;

        } else {
            $erreur = 'Adresse email ou mot de passe incorrect.';
        }
    }
}

$titrePage = 'Connexion';
require_once __DIR__ . '/../../header.php';
?>

<h1>Se connecter</h1>

<form method="post" action="connexion.php" class="formulaire">

    <?php if ($erreur !== ''): ?>
        <div class="message message-erreur"><?= proteger($erreur) ?></div>
    <?php endif; ?>

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
    </p>
    <div class="actions">
        <button type="submit" class="bouton">Se connecter</button>
        <a href="inscription.php">Créer un compte</a>
    </div>
</form>

<?php require_once __DIR__ . '/../../footer.php'; ?>