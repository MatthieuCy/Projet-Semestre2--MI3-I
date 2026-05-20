<?php
// api_plats.php — Retourne les plats filtrés en JSON (pour les requêtes asynchrones JS)
require_once(__DIR__ . '/includes/session.php');
require_once(__DIR__ . '/includes/donnees.php');

header('Content-Type: application/json; charset=utf-8');

$categorie  = $_GET['cat']    ?? 'toutes';
$regime     = $_GET['regime'] ?? '';

$plats = get_tous_plats();

// Comptage des commandes par plat
$comptage = [];
foreach (get_toutes_commandes() as $cmd) {
    foreach ($cmd['articles'] ?? [] as $art) {
        if ($art['type'] === 'plat') {
            $comptage[$art['id']] = ($comptage[$art['id']] ?? 0) + ($art['quantite'] ?? 1);
        }
    }
}
foreach ($plats as &$p) {
    $p['nb_commandes'] = $comptage[$p['id']] ?? 0;
}
unset($p);

// Filtrage catégorie
if ($categorie !== 'toutes') {
    $plats = array_filter($plats, fn($p) => $p['categorie'] === $categorie);
}

// Filtrage régime
if ($regime === 'sans_gluten') {
    $plats = array_filter($plats, fn($p) => $p['sans_gluten'] === true);
} elseif ($regime === 'sans_lactose') {
    $plats = array_filter($plats, fn($p) => $p['sans_lactose'] === true);
} elseif ($regime === 'vegetarien') {
    $plats = array_filter($plats, fn($p) => $p['vegetarien'] === true);
} elseif ($regime === 'vegan') {
    $plats = array_filter($plats, fn($p) => $p['vegan'] === true);
} elseif ($regime === 'halal') {
    $plats = array_filter($plats, fn($p) => $p['halal'] === true);
}

echo json_encode(array_values($plats), JSON_UNESCAPED_UNICODE);
