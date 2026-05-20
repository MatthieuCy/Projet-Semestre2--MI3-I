<?php
require_once(__DIR__ . '/includes/session.php');
require_once(__DIR__ . '/includes/donnees.php');
exiger_connexion();

$u    = get_utilisateur_connecte();
$role = $u['role'];

// Rediriige les rôles vers leur page 
if ($role === 'restaurateur') { header('Location: restaurateur.php'); exit; }
if ($role === 'livreur')      { header('Location: livraison.php');    exit; }
if ($role === 'admin')        { header('Location: admin.php');        exit; }

// PAGE CLIENT
$commandes_brutes = get_commandes_client($u['id']);
$commandes = array_values($commandes_brutes);
usort($commandes, fn($a, $b) => strcmp($b['date_commande'], $a['date_commande']));

$statut_labels = [
    'en_attente'     => ' En attente',
    'en_preparation' => ' En préparation',
    'en_livraison'   => ' En livraison',
    'livree'         => ' Livrée',
    'annulee'        => ' Annulée',
];
$statut_classes = [
    'en_attente'     => 'status-wait',
    'en_preparation' => 'status-prep',
    'en_livraison'   => 'status-delivery',
    'livree'         => 'status-delivered',
    'annulee'        => 'status-cancelled',
];

$remise = $u['remise'] ?? 0;
$points = $u['points_fidelite'] ?? 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - Pizza Nova</title>
</head>
<body>
<?php $base = ''; require_once(__DIR__ . '/includes/nav.php'); ?>

<main class="profile-container">
    <section class="profile-header">
        <h2>Bienvenue, <?= htmlspecialchars($u['prenom']) ?> !</h2>
        <p>Gérez vos informations et profitez de vos avantages fidélité.</p>
    </section>
 
    <div class="profile-grid">

        <!-- Infos personnelles -->
      <aside class="profile-card">
        <h3>Mes Informations</h3>
        <?php
        $champs = [
              'nom'       => ['label'=>'Nom',      'val'=>$u['nom']],
              'prenom'    => ['label'=>'Prénom',    'val'=>$u['prenom']],
              'telephone' => ['label'=>'Téléphone', 'val'=>$u['telephone'] ?: 'Non renseigné'],
              'adresse'   => ['label'=>'Adresse',   'val'=>$u['adresse']   ?: 'Non renseignée'],
              'details'   => ['label'=>'Détails',   'val'=>$u['details']   ?: 'Aucun'],
        ];
        foreach ($champs as $cle => $info): ?>
        <div class="info-item" data-champ="<?= $cle ?>">
            <div>
                <strong><?= $info['label'] ?> :</strong>
                <span class="valeur-champ"><?= htmlspecialchars($info['val']) ?></span>
            </div>
            <span class="edit-icon" data-champ="<?= $cle ?>" title="Modifier">✎</span>
        </div>
        <?php endforeach; ?>
        <p class="note-phase">Cliquez sur ✎ pour modifier (sauvegarde automatique)</p>
       </aside>

        <!-- Points fidélité -->
        <section class="profile-card loyalty">
            <h3>Points Fidélité </h3>
            <div class="points-box">
                <strong><?= $points ?></strong> points cumulés
            </div>
            <?php if ($remise > 0): ?>
                <p><em>Vous bénéficiez de <strong><?= $remise ?>%</strong> de remise sur votre prochaine commande !</em></p>
            <?php else: ?>
                <p><em>Continuez à commander pour débloquer des remises !</em></p>
            <?php endif; ?>
            <p class="statut-compte">
                Statut : <span class="badge-statut badge-<?= $u['statut'] ?>"><?= ucfirst($u['statut']) ?></span>
            </p>
        </section>

        <!-- Historique commandes -->
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
                        <th>Articles</th>
                        <th>Total</th>
                        <th>Statut</th>
                        <th>Note</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($commandes as $cmd): ?>
                    <tr>
                        <td>#<?= $cmd['id'] ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($cmd['date_commande'])) ?></td>
                        <td>
                            <?php foreach ($cmd['articles'] as $art): ?>
                                <?= $art['quantite'] ?>× <?= htmlspecialchars($art['nom']) ?><br>
                            <?php endforeach; ?>
                        </td>
                        <td><?= number_format($cmd['total'], 2) ?> €</td>
                        <td>
                            <span class="<?= $statut_classes[$cmd['statut']] ?? '' ?>">
                                <?= $statut_labels[$cmd['statut']] ?? $cmd['statut'] ?>
                            </span>
                        </td>
                        <td>
                            <?php
                            $note = $cmd['note_produits'] ?? null;
                            if ($cmd['statut'] === 'livree' && $note === null && ($cmd['type'] ?? '') === 'livraison'):
                            ?>
                                <a href="notation.php?commande_id=<?= $cmd['id'] ?>" class="btn-ok">Noter</a>
                            <?php elseif ($note !== null): ?>
                                 <?= $note ?>/5
                            <?php elseif ($cmd['statut'] === 'en_attente'): ?>
                                <a href="modifier_commande.php?id=<?= $cmd['id'] ?>" class="btn-ok">✏️ Modifier</a>
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

        <a href="carte.php" class="btn-main">🍕 Commander maintenant</a>
</main>

<footer>
    <p>&copy; 2025-2026 Projet Pizza Nova -préING2- Ibrahim, Ikram &amp; Matthieu</p>
</footer>
</body>
</html>


