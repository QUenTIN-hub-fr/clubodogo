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
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Clubodogo</title>
</head>
<body>
    <h1>Creer un compte</h1>

    <?php if ($succes): ?>
        <p>Votre compte a bien ete cree. Vous pouvez maintenant vous connecter.</p>
        <p><a href="connexion.php">Se connecter</a></p>
    <?php else: ?>

        <?php if (!empty($erreurs)): ?>
            <ul>
                <?php foreach ($erreurs as $erreur): ?>
                    <li><?= proteger($erreur) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <form method="post" action="inscription.php">
            <p>
                <label for="nom">Nom</label><br>
                <input type="text" id="nom" name="nom" value="<?= proteger($nom ?? '') ?>" required>
            </p>
            <p>
                <label for="prenom">Prenom</label><br>
                <input type="text" id="prenom" name="prenom" value="<?= proteger($prenom ?? '') ?>" required>
            </p>
            <p>
                <label for="email">Adresse email</label><br>
                <input type="email" id="email" name="email" value="<?= proteger($email ?? '') ?>" required>
            </p>
            <p>
                <label for="mot_de_passe">Mot de passe</label><br>
                <input type="password" id="mot_de_passe" name="mot_de_passe" required>
            </p>
            <p>
                <label for="confirmation">Confirmer le mot de passe</label><br>
                <input type="password" id="confirmation" name="confirmation" required>
            </p>
            <p><button type="submit">Creer mon compte</button></p>
        </form>

        <p><a href="connexion.php">J'ai deja un compte</a></p>

    <?php endif; ?>
</body>
</html>