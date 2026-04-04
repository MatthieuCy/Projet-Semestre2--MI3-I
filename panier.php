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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['valider_commande'])) {
    $num_carte = $_POST['num_carte'] ?? ''; // On récupère le numéro

    if (empty($_SESSION['panier'])) {
        $message = 'Votre panier est vide.';
    } 
    // AJOUT DE LA VÉRIFICATION CYBANK
    elseif (!verifier_paiement_cybank($total_remise, $num_carte)) {
        $message = "❌ Erreur de paiement : Numéro de carte invalide ou refusé.";
    } 
    } else {
        $type_commande = $_POST['type_commande'];
        
        $adresse = 'sur_place';
        if ($type_commande === 'livraison') {
            $adresse = ($u['adresse']) ? $u['adresse'] : 'Non renseignée';
        }

        $details = "";
        if ($type_commande === 'livraison') {
            $details = ($u['details']) ? $u['details'] : '';
        }

        $date_souhaitee = $_POST['date_souhaitee'];
        if (empty($date_souhaitee)) {
            $date_souhaitee = date('Y-m-d H:i:s', strtotime('+45 minutes'));
        }

        $articles_commande = array();
        foreach ($_SESSION['panier'] as $item) {
            $articles_commande[] = array(
                'type'          => $item['type'],
                'id'            => $item['id'],
                'quantite'      => $item['quantite'],
                'nom'           => $item['nom'],
                'prix_unitaire' => $item['prix'],
            );
        }

        $nouvelle_commande = array(
            'client_id'                => $u['id'],
            'articles'                 => $articles_commande,
            'total'                    => round($total_remise, 2),
            'adresse_livraison'        => $adresse,
            'details_livraison'        => $details,
            'telephone_client'         => $u['telephone'],
            'type'                     => $type_commande,
            'statut'                   => 'en_attente',
            'livreur_id'               => null,
            'date_commande'            => date('Y-m-d H:i:s'),
            'date_livraison_souhaitee' => $date_souhaitee,
            'paiement_statut'          => 'paye',
        );

        $id_nouvelle_commande = ajouter_commande($nouvelle_commande);

        
        $u_db = get_utilisateur_par_id($u['id']);
        $u_db['points_fidelite'] += (int)$total_remise;
        sauvegarder_utilisateur($u_db);

        $_SESSION['panier'] = array();
        $commande_passee = true;
        $message = "Commande #" . $id_nouvelle_commande . " passée avec succès !";
    }
}
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

    <form method="post" action="panier.php" class="form-container">
        <h3>Finaliser ma commande</h3>

        <div class="form-group">
            <label>Type de commande</label>
            <div class="rating-options">
                <input type="radio" name="type_commande" id="tc_livraison" value="livraison" checked>
                <label for="tc_livraison">🛵 Livraison</label>
                
                <input type="radio" name="type_commande" id="tc_emporter" value="emporter">
                <label for="tc_emporter">🏃 À emporter</label>
                
                <input type="radio" name="type_commande" id="tc_surplace" value="sur_place">
                <label for="tc_surplace">🪑 Sur place</label>
            </div>
        </div>

        <div class="form-group">
            <label for="date_souhaitee">Date/heure souhaitée</label>
            <input type="datetime-local" id="date_souhaitee" name="date_souhaitee">
        </div>

        <?php if ($u['adresse']) { ?>
            <div class="citation-familiale">
                <p><strong>Adresse de livraison :</strong> <?php echo htmlspecialchars($u['adresse']); ?></p>
                <p><small><?php echo htmlspecialchars($u['details']); ?></small></p>
            </div>
        <?php } else { ?>
            <p>⚠️ Aucune adresse enregistrée. <a href="profil.php">Mettre à jour mon profil</a></p>
        <?php } ?>
                  
        <div class="form-group">
            <label for="num_carte">Numéro de carte (Paiement CYBank)</label>
            <input type="text" id="num_carte" name="num_carte" placeholder="Min. 12 caractères" required>
        </div>
        <div class="infos-pratiques">
            <strong>💳 Paiement CYBank </strong>
            <p>Total : <?php echo number_format($total_remise, 2); ?> €</p>
        </div>

        <button type="submit" name="valider_commande" class="btn-main">✅ Confirmer et payer</button>
    </form>
    <?php } ?>
</main>
<footer>
    <p>&copy; 2025-2026 Projet Pizza Nova - Ibrahim, Ikram &amp; Matthieu</p>
</footer>
</body>
</html>
