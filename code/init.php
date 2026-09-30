<?php
session_start();

require_once __DIR__ . '/bdd.php';

function estConnecte()
{
    return isset($_SESSION['utilisateur_id']);
}

function utilisateurConnecte()
{
    return $_SESSION['utilisateur'] ?? null;
}

function aLeRole($role)
{
    return estConnecte() && $_SESSION['utilisateur']['role'] === $role;
}

function proteger($e)
{
    return htmlspecialchars($e ?? '', ENT_QUOTES, 'UTF-8');
}

function exigerConnexion()
{
    if (!estConnecte()) {
        header('Location: /compte/connexion.php');
        exit;
    }
}

function exigerRole($role)
{
    exigerConnexion();
    if (!aLeRole($role)) {
        http_response_code(403);
        exit('Accès refusé.');
    }
}

function ageEnMois($dateNaissance, $dateReference)
{
    $naissance = new DateTime($dateNaissance);
    $reference = new DateTime($dateReference);
    $ecart = $naissance->diff($reference);
    return $ecart->y * 12 + $ecart->m;
}

function trancheAge($min, $max)
{
    if ($max === null) {
        return 'à partir de ' . $min . ' mois';
    }
    return 'de ' . $min . ' à ' . $max . ' mois';
}

// Le jeton est généré une seule fois par session et reste valable tant qu'elle dure
function jetonCsrf()
{
    if (!isset($_SESSION['jeton_csrf'])) {
        $_SESSION['jeton_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['jeton_csrf'];
}

function champJetonCsrf()
{
    return '<input type="hidden" name="jeton_csrf" value="' . jetonCsrf() . '">';
}

// Toute requête POST sans jeton valide est rejetée avant le moindre traitement
function verifierJetonCsrf()
{
    $recu = $_POST['jeton_csrf'] ?? '';

    if (!isset($_SESSION['jeton_csrf']) || !hash_equals($_SESSION['jeton_csrf'], $recu)) {
        http_response_code(403);
        exit('Requête refusée : jeton de sécurité invalide.');
    }
}