<?php
require_once(__DIR__ . '/includes/session.php');
require_once(__DIR__ . '/includes/donnees.php');

// Vérification connexion client
if (!est_connecte() || get_role_connecte() !== 'client') {
    header('Location: connexion.php');
    exit;
}

$u = get_utilisateur_connecte();
$commande_id = intval($_GET['id'] ?? 0);
$commande = get_commande_par_id($commande_id);

// Vérifications de sécurité
if (!$commande || $commande['client_id'] !== $u['id'] || $commande['statut'] !== 'en_attente') {
    header('Location: profil.php');
    exit;
}

$tous_plats = get_tous_plats();
$erreur = '';
$succes = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $articles_mis_a_jour = [];
    $total = 0;

    foreach ($tous_plats as $plat) {
        $qte = intval($_POST['qte_' . $plat['id']] ?? 0);
        if ($qte > 0) {
            $articles_mis_a_jour[] = [
                'type'         => 'plat',
                'id'           => $plat['id'],
                'quantite'     => $qte,
                'nom'          => $plat['nom'],
                'prix_unitaire'=> $plat['prix'],
            ];
            $total += $plat['prix'] * $qte;
        }
    }

    if (empty($articles_mis_a_jour)) {
        $erreur = 'Votre commande ne peut pas être vide.';
    } else {
        $commande['articles'] = $articles_mis_a_jour;
        $commande['total']    = round($total, 2);
        if (sauvegarder_commande($commande)) {
            $succes = 'Commande modifiée avec succès !';
            $commande = get_commande_par_id($commande_id); // Recharger
        } else {
            $erreur = 'Une erreur est survenue lors de la sauvegarde.';
        }
    }
}

// Construire un index des quantités actuelles
$qtes_actuelles = [];
foreach ($commande['articles'] as $art) {
    if ($art['type'] === 'plat') {
        $qtes_actuelles[$art['id']] = $art['quantite'];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier la commande #<?= $commande['id'] ?> - Pizza Nova</title>
</head>
<body>
<?php $base = ''; require_once(__DIR__ . '/includes/nav.php'); ?>
<main class="container">
    <h2>Modifier la commande #<?= $commande['id'] ?></h2>
    <p>Vous pouvez ajouter ou retirer des articles tant que votre commande est en attente de préparation.</p>

    <?php if ($erreur): ?>
        <p class="message-erreur"><?= htmlspecialchars($erreur) ?></p>
    <?php endif; ?>
    <?php if ($succes): ?>
        <p class="message-succes"><?= htmlspecialchars($succes) ?></p>
    <?php endif; ?>

    <form method="post" action="modifier_commande.php?id=<?= $commande['id'] ?>">
        <?php
        $categories = ['pizza' => '🍕 Pizzas', 'entree' => '🥗 Entrées', 'dessert' => '🍮 Desserts', 'boisson' => '🥤 Boissons'];
        foreach ($categories as $cat => $label):
            $plats_cat = array_filter($tous_plats, fn($p) => $p['categorie'] === $cat);
            if (empty($plats_cat)) continue;
        ?>
        <h3><?= $label ?></h3>
        <table style="width:100%;margin-bottom:16px;">
            <thead><tr><th>Plat</th><th>Prix</th><th>Quantité</th></tr></thead>
            <tbody>
            <?php foreach ($plats_cat as $plat): ?>
                <tr>
                    <td><?= htmlspecialchars($plat['nom']) ?></td>
                    <td><?= number_format($plat['prix'], 2) ?> €</td>
                    <td>
                        <input type="number" name="qte_<?= $plat['id'] ?>"
                               value="<?= $qtes_actuelles[$plat['id']] ?? 0 ?>"
                               min="0" max="20" style="width:60px;">
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endforeach; ?>

        <div style="display:flex;gap:12px;margin-top:20px;">
            <button type="submit" class="btn-main">💾 Enregistrer les modifications</button>
            <a href="profil.php" class="btn-ok">Annuler</a>
        </div>
    </form>
</main>
<footer>
    <p>&copy; 2025-2026 Projet Pizza Nova -préING2- Ibrahim, Ikram &amp; Matthieu</p>
</footer>
</body>
</html>
