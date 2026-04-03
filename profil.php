<?php
require_once(__DIR__ . '/includes/session.php');
require_once(__DIR__ . '/includes/donnees.php');
exiger_connexion();

$u = get_utilisateur_connecte();
$commandes_brutes = get_commandes_client($u['id']);
$commandes = array_values($commandes_brutes);

// Tri des commandes par date décroissante 
usort($commandes, function($a, $b) {
    if ($a['date_commande'] == $b['date_commande']) {
        return 0;
    }
    return ($b['date_commande'] > $a['date_commande']) ? 1 : -1;
});


$statut_labels = array(
    'en_attente'     => '⏳ En attente',
    'en_preparation' => '👨‍🍳 En préparation',
    'en_livraison'   => '🛵 En livraison',
    'livree'         => '✅ Livrée',
    'annulee'        => '❌ Annulée'
);

$statut_classes = array(
    'en_attente'     => 'status-wait',
    'en_preparation' => 'status-prep',
    'en_livraison'   => 'status-delivery',
    'livree'         => 'status-delivered',
    'annulee'        => 'status-cancelled'
);

$remise = $u['remise'];
$points = $u['points_fidelite'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - Pizza Nova</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body>
<?php 
    $base = '../';
    require_once(__DIR__ . '/includes/nav.php'); 
?>

<main class="profile-container">
    <section class="profile-header">
        <h2>Bienvenue sur votre espace, <?php echo htmlspecialchars($u['prenom']); ?> !</h2>
        <h3>Gérez vos informations et profitez de vos avantages fidélité.</h3>
    </section>

    <div class="profile-grid">
        <aside class="profile-card">
            <h3>Mes Informations ✎</h3>
            <div class="info-item">
                <div><strong>Nom :</strong> <span><?php echo htmlspecialchars($u['nom']); ?></span></div>
                <span class="edit-icon" title="Modifiable en Phase 3">✎</span>
            </div>
            <div class="info-item">
                <div><strong>Prénom :</strong> <span><?php echo htmlspecialchars($u['prenom']); ?></span></div>
                <span class="edit-icon" title="Modifiable en Phase 3">✎</span>
            </div>
            <div class="info-item">
                <div><strong>Email :</strong> <span><?php echo htmlspecialchars($u['login']); ?></span></div>
                <span class="edit-icon" title="Modifiable en Phase 3">✎</span>
            </div>
            <div class="info-item">
                <div><strong>Téléphone :</strong> <span><?php echo htmlspecialchars($u['telephone'] ? $u['telephone'] : 'Non renseigné'); ?></span></div>
                <span class="edit-icon" title="Modifiable en Phase 3">✎</span>
            </div>
            <div class="info-item">
                <div><strong>Adresse :</strong> <span><?php echo htmlspecialchars($u['adresse'] ? $u['adresse'] : 'Non renseignée'); ?></span></div>
                <span class="edit-icon" title="Modifiable en Phase 3">✎</span>
            </div>
            <div class="info-item">
                <div><strong>Détails :</strong> <span><?php echo htmlspecialchars($u['details'] ? $u['details'] : 'Aucun'); ?></span></div>
                <span class="edit-icon" title="Modifiable en Phase 3">✎</span>
            </div>
            <p class="note-phase"><em>✏️ La modification sera effective en Phase 3.</em></p>
        </aside>

        <section class="profile-card loyalty">
            <h3>Points Fidélité 🏆</h3>
            <div class="points-box">
                <strong><?php echo $points; ?></strong> points cumulés
            </div>
            <?php if ($remise > 0): ?>
                <p><em>Vous avez droit à <strong><?php echo $remise; ?>%</strong> de remise sur votre prochaine commande !</em></p>
            <?php else: ?>
                <p><em>Continuez à commander pour débloquer des remises !</em></p>
            <?php endif; ?>
            <p class="statut-compte">Statut : <span class="badge-statut badge-<?php echo $u['statut']; ?>"><?php echo ucfirst($u['statut']); ?></span></p>
        </section>

        <section class="profile-card history">
            <h3>Mes Commandes</h3>
            <?php if (empty($commandes)): ?>
                <p>Vous n'avez pas encore passé de commande. <a href="carte.php">Voir la carte</a></p>
            <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>N°</th>
                        <th>Date</th>
                        <th>Détails</th>
                        <th>Total</th>
                        <th>Statut</th>
                        <th>Note</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($commandes as $cmd): ?>
                    <tr>
                        <td>#<?php echo $cmd['id']; ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($cmd['date_commande'])); ?></td>
                        <td>
                            <?php foreach ($cmd['articles'] as $art): ?>
                                <?php echo $art['quantite']; ?>x <?php echo htmlspecialchars($art['nom']); ?><br>
                            <?php endforeach; ?>
                        </td>
                        <td><?php echo number_format($cmd['total'], 2); ?> €</td>
                        <td>
                            <?php 
                                $classe = isset($statut_classes[$cmd['statut']]) ? $statut_classes[$cmd['statut']] : '';
                                $label = isset($statut_labels[$cmd['statut']]) ? $statut_labels[$cmd['statut']] : $cmd['statut'];
                            ?>
                            <span class="<?php echo $classe; ?>">
                                <?php echo $label; ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($cmd['statut'] == 'livree' && $cmd['note_produits'] === null): ?>
                                <a href="notation.php?commande_id=<?php echo $cmd['id']; ?>" class="btn-ok">Noter</a>
                            <?php elseif ($cmd['note_produits'] !== null): ?>
                                ⭐ <?php echo $cmd['note_produits']; ?>/5
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </section>
    </div>

    <?php if ($u['role'] == 'client'): ?>
    <div style="text-align:center; margin-top:30px;">
        <a href="carte.php" class="btn-main">🍕 Commander maintenant</a>
    </div>
    <?php endif; ?>
</main>

<footer>
    <p>&copy; 2025-2026 Projet Pizza Nova -préING2- Ibrahim, Ikram &amp; Matthieu</p>
</footer>
</body>
</html>
