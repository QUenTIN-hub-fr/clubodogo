<?php
require_once __DIR__ . '/../../init.php';
exigerRole('proprietaire');

$utilisateur = utilisateurConnecte();
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$requete = $pdo->prepare('SELECT nom_chien FROM chien WHERE id = ? AND id_utilisateur = ?');
$requete->execute([$id, $utilisateur['id']]);
$chien = $requete->fetch();

if (!$chien) {
    http_response_code(404);
    exit('Chien introuvable.');
}

$inscriptions = $pdo->prepare('SELECT COUNT(*) AS total FROM inscription WHERE id_chien = ?');
$inscriptions->execute([$id]);
$nombreInscriptions = (int) $inscriptions->fetch()['total'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verifierJetonCsrf();

    // Les deux suppressions forment un tout : soit les deux aboutissent, soit aucune
    try {
        $pdo->beginTransaction();

        $pdo->prepare('DELETE FROM inscription WHERE id_chien = ?')->execute([$id]);

        $suppression = $pdo->prepare('DELETE FROM chien WHERE id = ? AND id_utilisateur = ?');
        $suppression->execute([$id, $utilisateur['id']]);

        $pdo->commit();

    } catch (PDOException $e) {
        $pdo->rollBack();
        exit("La suppression a échoué, aucune donnée n'a été modifiée.");
    }

    header('Location: /chiens/liste.php');
    exit;
}

$titrePage = 'Supprimer un chien';
require_once __DIR__ . '/../../header.php';
?>

<h1>Supprimer un chien</h1>

<div class="formulaire">
    <p>Voulez-vous vraiment supprimer <strong><?= proteger($chien['nom_chien']) ?></strong> ?</p>

    <?php if ($nombreInscriptions > 0): ?>
        <div class="message message-erreur">
            Ce chien est inscrit à <?= $nombreInscriptions ?> cours.
            Ces inscriptions seront également supprimées.
        </div>
    <?php endif; ?>

    <p>Cette action est définitive.</p>

    <form method="post">

        <?= champJetonCsrf() ?>

        <div class="actions">
            <button type="submit" class="bouton bouton-rouge">Supprimer définitivement</button>
            <a href="/chiens/liste.php">Annuler</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../footer.php'; ?>