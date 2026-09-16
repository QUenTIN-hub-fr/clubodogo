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

            header('Location: ../index.php');
            exit;

        } else {
            $erreur = 'Adresse email ou mot de passe incorrect.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Clubodogo</title>
</head>
<body>
    <h1>Se connecter</h1>

    <?php if ($erreur !== ''): ?>
        <p><?= proteger($erreur) ?></p>
    <?php endif; ?>

    <form method="post" action="connexion.php">
        <p>
            <label for="email">Adresse email</label><br>
            <input type="email" id="email" name="email" value="<?= proteger($email ?? '') ?>" required>
        </p>
        <p>
            <label for="mot_de_passe">Mot de passe</label><br>
            <input type="password" id="mot_de_passe" name="mot_de_passe" required>
        </p>
        <p><button type="submit">Se connecter</button></p>
    </form>

    <p><a href="inscription.php">Creer un compte</a></p>
</body>
</html>