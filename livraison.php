<?php
require_once(__DIR__ . '/includes/session.php');
require_once(__DIR__ . '/includes/donnees.php');

exiger_connexion();

$u       = get_utilisateur_connecte();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action      = $_POST['action'] ?? '';
    $commande_id = (int)($_POST['commande_id'] ?? 0);
    $commande    = get_commande_par_id($commande_id);

    if ($commande && $commande['livreur_id'] === $u['id']) {
        if ($action === 'livree') {
            $commande['statut']                   = 'livree';
            $commande['date_livraison_effective']  = date('Y-m-d H:i:s');
            // +10 points fidélité pour le client
            $client = get_utilisateur_par_id($commande['client_id']);
            if ($client) {
                $client['points_fidelite'] += 10;
                sauvegarder_utilisateur($client);
            }
            sauvegarder_commande($commande);
            $message = 'Livraison confirmée ! Le client a été notifié.';
        } elseif ($action === 'abandonnee') {
            $commande['statut'] = 'annulee';
            sauvegarder_commande($commande);
            $message = 'Livraison abandonnée (adresse introuvable).';
        }
    }
}

$commande_en_cours = get_commande_livreur($u['id']);
$client = $commande_en_cours ? get_utilisateur_par_id($commande_en_cours['client_id']) : null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Livraison - Pizza Nova</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body>
<?php $base = '../';
require_once(__DIR__ . '/includes/nav.php');?>
<main class="container-mobile">

    <?php if ($message): ?>
        <p class="message-succes"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <?php if (!$commande_en_cours): ?>
        <section class="entete-livraison">
            <h2>Aucune livraison assignée</h2>
            <p>En attente d'une nouvelle commande...</p>
        </section>
    <?php else: ?>
        <section class="entete-livraison">
            <h2>Course n°<?= $commande_en_cours['id'] ?></h2>
        </section>

        <section class="infos-client-livraison">
            <div class="carte-livraison">
                <h3>🏠 Destination</h3>
                <p><strong>Client :</strong>
                    <?= $client ? htmlspecialchars($client['prenom'].' '.$client['nom']) : 'Inconnu' ?>
                </p>
                <p><strong>📞 Tél :</strong>
                    <a href="tel:<?= htmlspecialchars($commande_en_cours['telephone_client']) ?>">
                        <?= htmlspecialchars($commande_en_cours['telephone_client']) ?>
                    </a>
                </p>
                <p><strong>📍 Adresse :</strong> <?= htmlspecialchars($commande_en_cours['adresse_livraison']) ?></p>
                <p><strong>🔑 Détails :</strong> <?= htmlspecialchars($commande_en_cours['details_livraison'] ?: 'Aucun') ?></p>
            </div>

            <div class="carte-livraison" style="margin-top:15px;">
                <h3>📦 Articles</h3>
                <ul>
                    <?php foreach ($commande_en_cours['articles'] as $art): ?>
                        <li><?= $art['quantite'] ?>x <?= htmlspecialchars($art['nom']) ?></li>
                    <?php endforeach; ?>
                </ul>
                <p><strong>Total :</strong> <?= number_format($commande_en_cours['total'], 2) ?> €</p>
                <p><strong>Paiement :</strong>
                    <?= $commande_en_cours['paiement_statut'] === 'paye' ? '✅ Déjà payé' : '⚠️ Non payé' ?>
                </p>
            </div>

            <?php
            $adresse_encoded = urlencode($commande_en_cours['adresse_livraison']);
            ?>
            <a href="https://www.google.com/maps/dir/?api=1&destination=<?= $adresse_encoded ?>"
               target="_blank" class="btn-gps" style="display:block; text-align:center; margin:15px 0;">
                🗺️ LANCER L'ITINÉRAIRE
            </a>

            <form method="post" action="livraison.php">
                <input type="hidden" name="commande_id" value="<?= $commande_en_cours['id'] ?>">
                <input type="hidden" name="action" value="livree">
                <button type="submit" class="btn-valider-livraison">✅ MARQUER COMME LIVRÉE</button>
            </form>

            <form method="post" action="livraison.php" >
                <input type="hidden" name="commande_id" value="<?= $commande_en_cours['id'] ?>">
                <input type="hidden" name="action" value="abandonnee">
                <button type="submit" class="btn-valider-livraison" >
                    ❌ ADRESSE INTROUVABLE
                </button>
            </form>
        </section>
    <?php endif; ?>
</main>
<footer class="footer-mobile">
    <p>&copy; 2025-2026 Projet Pizza Nova - Ibrahim, Ikram &amp; Matthieu</p>
</footer>
</body>
</html>                <p><strong>Adresse :</strong> 123 Rue de l'Université, 95000 Cergy</p>
                <p><strong>Détails :</strong> Bâtiment X, Xème étage, Code 01234</p>
            </div>

            <a href="https://www.google.com/maps" target="_blank" class="btn-gps">
                LANCER L'ITINÉRAIRE 
            </a>
            
            <button class="btn-valider-livraison">
                ✅ MARQUER COMME LIVRÉE
            </button>
        </section>
    </main>

    <footer class="footer-mobile">
        <p>&copy; 2025-2026 Projet Pizza Nova - Ibrahim, Ikram & Matthieu</p>
    </footer>

</body>
</html>
