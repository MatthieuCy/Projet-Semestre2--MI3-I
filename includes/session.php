<?php
// includes/session.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function est_connecte(): bool {
    return isset($_SESSION['utilisateur_id']);
}

function get_utilisateur_connecte(): ?array {
    if (!est_connecte()) return null;
    require_once __DIR__ . '/donnees.php';
    return get_utilisateur_par_id($_SESSION['utilisateur_id']);
}

function get_role_connecte(): ?string {
    $u = get_utilisateur_connecte();
    return $u ? $u['role'] : null;
}

function exiger_connexion(): void {
    if (!est_connecte()) {
        header('Location: connexion.php');
        exit;
    }
}

function exiger_role(string $role): void {
    exiger_connexion();
    $r = get_role_connecte();
    if ($r !== $role && $r !== 'admin') {
        header('Location: index.php');
        exit;
    }
}

function connecter_utilisateur(array $utilisateur): void {
    $_SESSION['utilisateur_id'] = $utilisateur['id'];
    $_SESSION['role']           = $utilisateur['role'];
}

function deconnecter(): void {
    session_destroy();
    header('Location: connexion.php');
    exit;
}
