<?php
require_once __DIR__ . '/../../init.php';
exigerRole('responsable');

$succes = '';
$erreurs = [];

$rolesAutorises = ['responsable', 'coach', 'proprietaire'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idMembre = (int) ($_POST['id_membre'] ?? 0);
    $nouveauRole = $_POST['role'] ?? '';

    // Le responsable connecté ne peut pas modifier son propre rôle : il perdrait l'accès au back-office
    if ($idMembre === (int) $_SESSION['utilisateur']['id']) {
        $erreurs[] = "Vous ne pouvez pas modifier votre propre rôle.";
    } elseif (!in_array($nouveauRole, $rolesAutorises, true)) {
        $erreurs[] = "Ce rôle n'existe pas.";
    } else {
        $requete = $pdo->prepare('SELECT id FROM utilisateur WHERE id = ?');
        $requete->execute([$idMembre]);

        if (!$requete->fetch()) {
            $erreurs[] = "Ce membre n'existe pas.";
        } else {
            $pdo->prepare('UPDATE utilisateur SET role = ? WHERE id = ?')->execute([$nouveauRole, $idMembre]);
            $succes = 'Le rôle a bien été modifié.';
        }
    }
}

$membres = $pdo->query(
    "SELECT utilisateur.id, utilisateur.nom, utilisateur.prenom, utilisateur.email, utilisateur.role,
            (SELECT COUNT(*) FROM chien WHERE chien.id_utilisateur = utilisateur.id) AS nb_chiens
     FROM utilisateur
     ORDER BY utilisateur.nom, utilisateur.prenom"
)->fetchAll();

$titrePage = 'Gestion des membres';
require_once __DIR__ . '/../../header-admin.php';
?>

<h1>Gestion des membres</h1>

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

<?php if (empty($membres)): ?>
    <p class="vide">Aucun membre enregistré.</p>
<?php else: ?>
    <table class="tableau">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Email</th>
                <th>Chiens</th>
                <th>Rôle</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($membres as $membre): ?>
                <tr>
                    <td data-label="Nom"><?= proteger($membre['prenom'] . ' ' . $membre['nom']) ?></td>
                    <td data-label="Email"><?= proteger($membre['email']) ?></td>
                    <td data-label="Chiens"><?= (int) $membre['nb_chiens'] ?></td>
                    <td data-label="Rôle">
                        <?php if ((int) $membre['id'] === (int) $_SESSION['utilisateur']['id']): ?>
                            <span class="role-actuel"><?= proteger($membre['role']) ?> (vous)</span>
                        <?php else: ?>
                            <form method="post" class="formulaire-ligne">
                                <input type="hidden" name="id_membre" value="<?= (int) $membre['id'] ?>">
                                <select name="role">
                                    <?php foreach ($rolesAutorises as $role): ?>
                                        <option value="<?= $role ?>" <?= $membre['role'] === $role ? 'selected' : '' ?>>
                                            <?= ucfirst($role) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="bouton bouton-petit">Enregistrer</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require_once __DIR__ . '/../../footer.php'; ?>