<?php
// Modifie infos profil en asynchrone (fetch JS)
require_once(__DIR__ . '/includes/session.php');
require_once(__DIR__ . '/includes/donnees.php');

header('Content-Type: application/json; charset=utf-8');

exiger_connexion();
$u = get_utilisateur_connecte();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['succes' => false, 'message' => 'Méthode non autorisée.']);
    exit;
}

$body  = file_get_contents('php://input');
$data  = json_decode($body, true);
$champ = $data['champ'] ?? '';
$valeur = trim($data['valeur'] ?? '');

// Champs autorisés à modifier
$champs_autorises = ['nom', 'prenom', 'telephone', 'adresse', 'details'];

if (!in_array($champ, $champs_autorises)) {
    echo json_encode(['succes' => false, 'message' => 'Champ non modifiable.']);
    exit;
}

// Validations spécifiques
if ($champ === 'telephone' && $valeur && !preg_match('/^0[1-9][0-9]{8}$/', preg_replace('/\s/', '', $valeur))) {
    echo json_encode(['succes' => false, 'message' => 'Numéro de téléphone invalide.']);
    exit;
}
if (in_array($champ, ['nom', 'prenom']) && empty($valeur)) {
    echo json_encode(['succes' => false, 'message' => 'Ce champ ne peut pas être vide.']);
    exit;
}

// Recharger  données utilisateur depuis le fichier JSON
$u_frais = get_utilisateur_par_id($u['id']);
$u_frais[$champ] = $valeur;
$ok = sauvegarder_utilisateur($u_frais);

if ($ok) {
    echo json_encode(['succes' => true, 'message' => 'Modification enregistrée !']);
} else {
    echo json_encode(['succes' => false, 'message' => 'Erreur lors de la sauvegarde.']);
}
