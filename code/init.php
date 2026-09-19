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
        exit('Acces refuse.');
    }
}