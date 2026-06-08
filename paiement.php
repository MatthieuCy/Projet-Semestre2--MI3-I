<?php
require_once(__DIR__ . '/includes/session.php');
require_once(__DIR__ . '/includes/donnees.php');
require_once(__DIR__ . '/includes/getapikey.php');

exiger_connexion();
$u = get_utilisateur_connecte();

if (empty($_SESSION['panier'])) {
    header('Location: panier.php');
    exit;
}

// Calcul du montant
$total = 0;
foreach ($_SESSION['panier'] as $item) {
    $total += ($item['prix'] * $item['quantite']);
}
$remise_pourcent  = $u['remise'] ?? 0;
$total_remise     = $total - ($total * ($remise_pourcent / 100));
$montant_formatte = number_format($total_remise, 2, '.', '');

// CYBank
$vendeur     = "MI-3_I";
$transaction = substr(md5(uniqid(mt_rand(), true)), 0, 15);
$protocole   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$url_retour  = $protocole . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . '/retour_paiement.php';

// Calcul du hash MD5 de contrôle
$api_key = getAPIKey($vendeur);
$control = md5($api_key . "#" . $transaction . "#" . $montant_formatte . "#" . $vendeur . "#" . $url_retour . "#");

// On stocke les infos de commande en session
$_SESSION['temp_commande'] = [
    'type_commande'  => $_POST['type_commande']  ?? 'livraison',
    'date_souhaitee' => $_POST['date_souhaitee'] ?? ''
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Redirection Paiement - Pizza Nova</title>
</head>
<body>
<?php $base = ''; require_once(__DIR__ . '/includes/nav.php'); ?>
<main class="container">
    <div class="form-container">
        <h2>Redirection vers CYBank...</h2>
        <p>Montant : <?= $montant_formatte ?> €</p>
        <form action="https://www.plateforme-smc.fr/cybank/index.php" method="POST">
            <input type="hidden" name="transaction" value="<?= $transaction ?>">
            <input type="hidden" name="montant"     value="<?= $montant_formatte ?>">
            <input type="hidden" name="vendeur"     value="<?= $vendeur ?>">
            <input type="hidden" name="retour"      value="<?= htmlspecialchars($url_retour) ?>">
            <input type="hidden" name="control"     value="<?= $control ?>">
            <button type="submit" class="btn-main">Payer maintenant</button>
        </form>
    </div>
</main>
<footer>
    <p>&copy; 2025-2026 Projet Pizza Nova -préING2- Ibrahim, Ikram &amp; Matthieu</p>
</footer>
</body>
</html>
