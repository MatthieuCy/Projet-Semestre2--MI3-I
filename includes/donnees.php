<?php
// includes/donnees.php
// Bibliothèque de fonctions pour lire/écrire les données JSON

define('DATA_DIR', __DIR__ . '/../data/');

function lire_json(string $fichier): array {
    $chemin = DATA_DIR . $fichier;
    if (!file_exists($chemin)) return [];
    $contenu = file_get_contents($chemin);
    return json_decode($contenu, true) ?? [];
}

function ecrire_json(string $fichier, array $donnees): bool {
    $chemin = DATA_DIR . $fichier;
    return file_put_contents($chemin, json_encode($donnees, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false;
}

// -- UTILISATEURS --
function get_tous_utilisateurs(): array {
    return lire_json('utilisateurs.json');
}

function get_utilisateur_par_id(int $id): ?array {
    foreach (get_tous_utilisateurs() as $u) {
        if ($u['id'] === $id) return $u;
    }
    return null;
}

function get_utilisateur_par_login(string $login): ?array {
    foreach (get_tous_utilisateurs() as $u) {
        if ($u['login'] === $login) return $u;
    }
    return null;
}

function sauvegarder_utilisateur(array $utilisateur): bool {
    $tous = get_tous_utilisateurs();
    foreach ($tous as $i => $u) {
        if ($u['id'] === $utilisateur['id']) {
            $tous[$i] = $utilisateur;
            return ecrire_json('utilisateurs.json', $tous);
        }
    }
    return false;
}

function ajouter_utilisateur(array $nouvel_utilisateur): bool {
    $tous = get_tous_utilisateurs();
    $max_id = 0;
    foreach ($tous as $u) { if ($u['id'] > $max_id) $max_id = $u['id']; }
    $nouvel_utilisateur['id'] = $max_id + 1;
    $tous[] = $nouvel_utilisateur;
    return ecrire_json('utilisateurs.json', $tous);
}

function login_existe(string $login): bool {
    return get_utilisateur_par_login($login) !== null;
}

// -- PLATS --
function get_tous_plats(): array {
    return lire_json('plats.json');
}

function get_plat_par_id(int $id): ?array {
    foreach (get_tous_plats() as $p) {
        if ($p['id'] === $id) return $p;
    }
    return null;
}

// -- MENUS --
function get_tous_menus(): array {
    return lire_json('menus.json');
}

function get_menu_par_id(int $id): ?array {
    foreach (get_tous_menus() as $m) {
        if ($m['id'] === $id) return $m;
    }
    return null;
}

// -- COMMANDES --
function get_toutes_commandes(): array {
    return lire_json('commandes.json');
}

function get_commandes_client(int $client_id): array {
    return array_filter(get_toutes_commandes(), fn($c) => $c['client_id'] === $client_id);
}

function get_commande_par_id(int $id): ?array {
    foreach (get_toutes_commandes() as $c) {
        if ($c['id'] === $id) return $c;
    }
    return null;
}

function get_commandes_par_statut(string $statut): array {
    return array_filter(get_toutes_commandes(), fn($c) => $c['statut'] === $statut);
}

function get_commande_livreur(int $livreur_id): ?array {
    foreach (get_toutes_commandes() as $c) {
        if (($c['livreur_id'] ?? null) === $livreur_id && $c['statut'] === 'en_livraison') return $c;
    }
    return null;
}

function sauvegarder_commande(array $commande): bool {
    $toutes = get_toutes_commandes();
    foreach ($toutes as $i => $c) {
        if ($c['id'] === $commande['id']) {
            $toutes[$i] = $commande;
            return ecrire_json('commandes.json', $toutes);
        }
    }
    return false;
}

function ajouter_commande(array $nouvelle_commande): int {
    $toutes = get_toutes_commandes();
    $max_id = 0;
    foreach ($toutes as $c) { if ($c['id'] > $max_id) $max_id = $c['id']; }
    $nouvelle_commande['id'] = $max_id + 1;
    $toutes[] = $nouvelle_commande;
    ecrire_json('commandes.json', $toutes);
    return $nouvelle_commande['id'];
}

function get_livreurs_disponibles(): array {
    $tous = get_tous_utilisateurs();
    $livreurs = array_filter($tous, fn($u) => $u['role'] === 'livreur');
    // Un livreur est disponible s'il n'a pas de commande "en_livraison" assignée
    $occupes = [];
    foreach (get_toutes_commandes() as $c) {
        if ($c['statut'] === 'en_livraison' && $c['livreur_id'] !== null) {
            $occupes[] = $c['livreur_id'];
        }
    }
    return array_filter($livreurs, fn($l) => !in_array($l['id'], $occupes));
}
