<?php
// api_admin.php — Actions admin en asynchrone (bloquer/débloquer)
require_once(__DIR__ . '/includes/session.php');
require_once(__DIR__ . '/includes/donnees.php');
require_once(__DIR__ . '/includes/logs.php');

header('Content-Type: application/json; charset=utf-8');

exiger_role('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['succes' => false, 'message' => 'Méthode non autorisée.']);
    exit;
}

$body   = file_get_contents('php://input');
$data   = json_decode($body, true);
$action = $data['action'] ?? '';
$uid    = (int)($data['user_id'] ?? 0);

$cible = get_utilisateur_par_id($uid);
if (!$cible) {
    echo json_encode(['succes' => false, 'message' => 'Utilisateur introuvable.']);
    exit;
}

$connecte = get_utilisateur_connecte();
if ($cible['id'] === $connecte['id']) {
    echo json_encode(['succes' => false, 'message' => 'Vous ne pouvez pas vous bloquer vous-même.']);
    exit;
}

if ($action === 'bloquer') {
    $cible['statut'] = 'bloque';
    sauvegarder_utilisateur($cible);
    enregistrer_log('blocage_compte', $cible['login'], "Compte bloqué par l'admin (id: {$admin['id']}).");
    echo json_encode(['succes' => true, 'message' => 'Compte bloqué.', 'nouveau_statut' => 'bloque']);
    
} elseif ($action === 'activer') {
    $cible['statut'] = 'actif';
    sauvegarder_utilisateur($cible);
     enregistrer_log('deblocage_compte', $cible['login'], "Compte débloqué par l'admin (id: {$admin['id']}).");
    echo json_encode(['succes' => true, 'message' => 'Compte activé.', 'nouveau_statut' => 'actif']);
  
} else {
    echo json_encode(['succes' => false, 'message' => 'Action inconnue.']);
}
