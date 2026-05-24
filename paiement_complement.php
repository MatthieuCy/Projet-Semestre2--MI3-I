<?php
require_once(__DIR__ . '/includes/session.php');
require_once(__DIR__ . '/includes/donnees.php');
require_once(__DIR__ . '/includes/getapikey.php');

exiger_connexion();
$u = get_utilisateur_connecte();

if (empty($_SESSION['paiement_complement'])) {
    header('Location: profil.php');
    exit;
}

$complement       = $_SESSION['paiement_complement'];
$montant_formatte = number_format($complement['montant'], 2, '.', '');
$commande_id      = $complement['commande_id'];

$vendeur     = "MI-3_I";
$transaction = substr(md5(uniqid(mt_rand(), true)), 0, 15);
$protocole   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$url_retour  = $protocole . '://' . $_SERVER['HTTP_HOST'] . '/retour_paiement.php?complement=1&id=' . $commande_id;

$api_key = getAPIKey($vendeur);
$control = md5($api_key . "#" . $transaction . "#" . $montant_formatte . "#" . $vendeur . "#" . $url_retour . "#");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Paiement complémentaire - Pizza Nova</title>
</head>
<body>
<?php $base = ''; require_once(__DIR__ . '/includes/nav.php'); ?>
<main class="container">
    <div class="form-container">
        <h2>Paiement complémentaire</h2>
        <p>Votre commande a été modifiée à la hausse. Un paiement complémentaire de <strong><?= $montant_formatte ?> €</strong> est requis.</p>
        <form action="https://www.plateforme-smc.fr/cybank/index.php" method="POST">
            <input type="hidden" name="transaction" value="<?= $transaction ?>">
            <input type="hidden" name="montant"     value="<?= $montant_formatte ?>">
            <input type="hidden" name="vendeur"     value="<?= $vendeur ?>">
            <input type="hidden" name="retour"      value="<?= htmlspecialchars($url_retour) ?>">
            <input type="hidden" name="control"     value="<?= $control ?>">
            <button type="submit" class="btn-main">Payer la différence</button>
        </form>
        <a href="profil.php" class="btn-ok btn-annuler">Annuler</a>
    </div>
</main>
<footer>
    <p>&copy; 2025-2026 Projet Pizza Nova -préING2- Ibrahim, Ikram &amp; Matthieu</p>
</footer>
</body>
</html>
