<?php
// api_restaurateur.php — Changement de statut commande en asynchrone
require_once(__DIR__ . '/includes/session.php');
require_once(__DIR__ . '/includes/donnees.php');

header('Content-Type: application/json; charset=utf-8');

exiger_role('restaurateur');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['succes' => false, 'message' => 'Méthode non autorisée.']);
    exit;
}

$body       = file_get_contents('php://input');
$data       = json_decode($body, true);
$action     = $data['action']       ?? '';
$id_cmd     = (int)($data['commande_id']  ?? 0);
$id_livreur = (int)($data['livreur_id']   ?? 0);

$commande = get_commande_par_id($id_cmd);
if (!$commande) {
    echo json_encode(['succes' => false, 'message' => 'Commande introuvable.']);
    exit;
}

if ($action === 'lancer' && $commande['statut'] === 'en_attente') {
    $commande['statut'] = 'en_preparation';
    sauvegarder_commande($commande);
    echo json_encode([
        'succes'         => true,
        'message'        => "Commande #$id_cmd lancée en cuisine.",
        'nouveau_statut' => 'en_preparation'
    ]);

} elseif ($action === 'pret' && $commande['statut'] === 'en_preparation') {
    if ($commande['type'] === 'livraison') {
        if ($id_livreur <= 0) {
            echo json_encode(['succes' => false, 'message' => 'Veuillez sélectionner un livreur.']);
            exit;
        }
        $commande['livreur_id'] = $id_livreur;
        $commande['statut']     = 'en_livraison';
        sauvegarder_commande($commande);
        $infos_livreur = get_utilisateur_par_id($id_livreur);
        echo json_encode([
            'succes'         => true,
            'message'        => "Commande #$id_cmd confiée à {$infos_livreur['prenom']} {$infos_livreur['nom']}.",
            'nouveau_statut' => 'en_livraison'
        ]);
    } else {
        $commande['statut']                  = 'livree';
        $commande['date_livraison_effective'] = date('Y-m-d H:i:s');
        sauvegarder_commande($commande);
        $client = get_utilisateur_par_id($commande['client_id']);
        if ($client) {
            $client['points_fidelite'] += 10;
            sauvegarder_utilisateur($client);
        }
        echo json_encode([
            'succes'         => true,
            'message'        => "Commande #$id_cmd clôturée.",
            'nouveau_statut' => 'livree'
        ]);
    }
} else {
    echo json_encode(['succes' => false, 'message' => 'Action invalide.']);
}
