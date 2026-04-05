<?php
require_once(__DIR__ . '/includes/session.php');
require_once(__DIR__ . '/includes/donnees.php');
require_once(__DIR__ . '/includes/getapikey.php');

$u = get_utilisateur_connecte();
$vendeur = "MI-3_I";
$api_key = getAPIKey($vendeur);

// Récupération des données renvoyées par CYBank en GET 
$transaction = $_GET['transaction'] ?? '';
$montant = $_GET['montant'] ?? '';
$statut = $_GET['status'] ?? ''; 
$control_recu = $_GET['control'] ?? '';

// Vérification de sécurité
$control_verif = md5($api_key . "#" . $transaction . "#" . $montant . "#" . $vendeur . "#" . $statut . "#");

if ($statut === 'accepted' && $control_recu === $control_verif) {
    // On crée la commande dans le JSON 
    $temp = $_SESSION['temp_commande'];
    
    $articles = [];
    foreach ($_SESSION['panier'] as $item) {
        $articles[] = [
            'type' => $item['type'],
            'id' => $item['id'],
            'quantite' => $item['quantite'],
            'nom' => $item['nom'],
            'prix_unitaire' => $item['prix']
        ];
    }

    $nouvelle_commande = [
        'client_id' => $u['id'],
        'articles' => $articles,
        'total' => (float)$montant,
        'adresse_livraison' => ($temp['type_commande'] === 'livraison') ? ($u['adresse'] ?? 'Non renseignée') : 'sur_place',
        'type' => $temp['type_commande'],
        'statut' => 'en_attente',
        'date_commande' => date('Y-m-d H:i:s'),
        'date_livraison_souhaitee' => $temp['date_souhaitee'],
        'paiement_statut' => 'paye'
    ];

    ajouter_commande($nouvelle_commande); 
    
    $_SESSION['panier'] = [];
    $message = "Commande validée avec succès !";
} else {
    $message = "Erreur : Le paiement a été refusé.";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Confirmation de commande</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main class="container">
        <div class="form-container">
            <h1><?php echo $statut === 'accepted' ? '✅ Merci !' : '❌ Erreur'; ?></h1>
            <p><?php echo $message; ?></p>
            <br>
            <a href="index.php" class="btn-main">Retour à l'accueil</a>
        </div>
    </main>
</body>
</html>
