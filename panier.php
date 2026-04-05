<?php
require_once(__DIR__ . '/includes/session.php');
require_once(__DIR__ . '/includes/donnees.php');
exiger_connexion();

$u = get_utilisateur_connecte();

if ($u['role'] !== 'client') { 
    header('Location: index.php'); 
    exit; 
}

if (isset($_SESSION['panier']) == false) {
    $_SESSION['panier'] = array();
}

$message = '';


$action = "";
if (isset($_GET['action'])) { 
    $action = $_GET['action']; 
}

if ($action === 'ajouter') {
    $type = "";
    if (isset($_GET['type'])) { $type = $_GET['type']; }
    $id = (int)$_GET['id'];

    if ($type === 'plat') {
        $item = get_plat_par_id($id);
        if ($item) {
            $prix = $item['prix'];
            $nom  = $item['nom'];
        }
    } elseif ($type === 'menu') {
        $item = get_menu_par_id($id);
        if ($item) {
            $prix = $item['prix_total'];
            $nom  = $item['nom'];
        }
    }

    if (isset($item) && $item != null) {
        $cle = $type . '_' . $id;
        if (isset($_SESSION['panier'][$cle])) {
            $_SESSION['panier'][$cle]['quantite']++;
        } else {
            $_SESSION['panier'][$cle] = array(
                'type'     => $type,
                'id'       => $id,
                'nom'      => $nom,
                'prix'     => $prix,
                'quantite' => 1,
            );
        }
        $message = htmlspecialchars($nom) . ' ajouté au panier !';
    }
}

if ($action === 'retirer') {
    $cle = $_GET['cle'];
    unset($_SESSION['panier'][$cle]);
}

if ($action === 'vider') {
    $_SESSION['panier'] = array();
}


$total = 0;
$remise_pourcent = $u['remise'];

foreach ($_SESSION['panier'] as $item) {
    $total = $total + ($item['prix'] * $item['quantite']);
}

$montant_remise = $total * ($remise_pourcent / 100);
$total_remise = $total - $montant_remise;


$commande_passee = false;
$id_nouvelle_commande = null;

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Panier - Pizza Nova</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php 
    $base = '../';
    require_once(__DIR__ . '/includes/nav.php'); 
?>
<main class="container">
    <h1>Mon Panier 🛒</h1>

    <?php if ($message != "") { ?>
        <p class="message-succes"><?php echo $message; ?></p>
    <?php } ?>

    <?php if ($commande_passee) { ?>
        <div class="form-container">
            <h2>✅ Commande confirmée !</h2>
            <p>Commande n°<strong><?php echo $id_nouvelle_commande; ?></strong> enregistrée.</p>
            <a href="profil.php" class="btn-main">Suivre ma commande</a>
        </div>
    <?php } elseif (empty($_SESSION['panier'])) { ?>
        <div class="form-container">
            <p>Votre panier est vide.</p>
            <a href="carte.php" class="btn-main">Voir la carte</a>
        </div>
    <?php } else { ?>

    <table>
        <thead>
            <tr>
                <th>Produit</th>
                <th>Prix unitaire</th>
                <th>Quantité</th>
                <th>Sous-total</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($_SESSION['panier'] as $cle => $item) { ?>
            <tr>
                <td><?php echo htmlspecialchars($item['nom']); ?> <small>(<?php echo $item['type']; ?>)</small></td>
                <td><?php echo number_format($item['prix'], 2); ?> €</td>
                <td><?php echo $item['quantite']; ?></td>
                <td><?php echo number_format($item['prix'] * $item['quantite'], 2); ?> €</td>
                <td>
                    <a href="panier.php?action=retirer&cle=<?php echo urlencode($cle); ?>" class="btn-ok">✕</a>
                </td>
            </tr>
        <?php } ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3"><strong>Total brut</strong></td>
                <td colspan="2"><strong><?php echo number_format($total, 2); ?> €</strong></td>
            </tr>
            <?php if ($remise_pourcent > 0) { ?>
            <tr>
                <td colspan="3"><em>Remise fidélité (<?php echo $remise_pourcent; ?>%)</em></td>
                <td colspan="2"><em>-<?php echo number_format($montant_remise, 2); ?> €</em></td>
            </tr>
            <tr>
                <td colspan="3"><strong>Total à payer</strong></td>
                <td colspan="2"><strong><?php echo number_format($total_remise, 2); ?> €</strong></td>
            </tr>
            <?php } ?>
        </tfoot>
    </table>

    <a href="panier.php?action=vider" class="btn-ok">🗑️ Vider le panier</a>

    <form method="post" action="paiement.php" class="form-container">
    <h3>Finaliser ma commande</h3>
    
    <div class="form-group">
        <label>Type de commande</label>
        <input type="radio" name="type_commande" value="livraison" checked> Livraison
        <input type="radio" name="type_commande" value="emporter"> À emporter
    </div>

    <div class="form-group">
        <label for="date_souhaitee">Date/heure souhaitée</label>
        <input type="datetime-local" name="date_souhaitee">
    </div>

    <button type="submit" class="btn-main">✅ Aller au paiement sécurisé CYBank</button>
    </form>
    <?php } ?>
</main>
<footer>
    <p>&copy; 2025-2026 Projet Pizza Nova - Ibrahim, Ikram &amp; Matthieu</p>
</footer>
</body>
</html>
