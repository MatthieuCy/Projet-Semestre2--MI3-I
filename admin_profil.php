<?php
require_once '../includes/session.php';
require_once '../includes/donnees.php';
exiger_role('admin');

$id = (int)($_GET['id'] ?? 0);
$u  = get_utilisateur_par_id($id);
if (!$u) { header('Location: admin.php'); exit; }

$commandes = array_values(get_commandes_client($u['id']));
usort($commandes, fn($a, $b) => strcmp($b['date_commande'], $a['date_commande']));

$statut_labels = [
    'en_attente'     => '⏳ En attente',
    'en_preparation' => '👨‍🍳 En préparation',
    'en_livraison'   => '🛵 En livraison',
    'livree'         => '✅ Livrée',
    'annulee'        => '❌ Annulée',
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Profil Utilisateur - Admin</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
<?php $base = '../';
require_once '../includes/nav.php'; ?>
<main class="profile-container">
    <h1>Profil de <?= htmlspecialchars($u['prenom'] . ' ' . $u['nom']) ?></h1>
    <a href="admin.php" class="btn-ok" style="margin-bottom:20px; display:inline-block;">← Retour</a>

    <div class="profile-grid">
        <aside class="profile-card">
            <h3>Informations</h3>
            <div class="info-item"><strong>ID :</strong> <?= $u['id'] ?></div>
            <div class="info-item"><strong>Email :</strong> <?= htmlspecialchars($u['login']) ?></div>
            <div class="info-item"><strong>Rôle :</strong> <?= htmlspecialchars($u['role']) ?></div>
            <div class="info-item"><strong>Téléphone :</strong> <?= htmlspecialchars($u['telephone']) ?></div>
            <div class="info-item"><strong>Adresse :</strong> <?= htmlspecialchars($u['adresse']) ?></div>
            <div class="info-item"><strong>Détails :</strong> <?= htmlspecialchars($u['details']) ?></div>
            <div class="info-item"><strong>Inscription :</strong> <?= $u['date_inscription'] ?></div>
            <div class="info-item"><strong>Dernière connexion :</strong> <?= $u['derniere_connexion'] ?></div>
            <div class="info-item"><strong>Statut :</strong> <span class="badge-statut badge-<?= $u['statut'] ?>"><?= ucfirst($u['statut']) ?></span></div>
            <div class="info-item"><strong>Points fidélité :</strong> <?= $u['points_fidelite'] ?></div>
            <div class="info-item"><strong>Remise :</strong> <?= $u['remise'] ?>%</div>
        </aside>

        <section class="profile-card history">
            <h3>Commandes (<?= count($commandes) ?>)</h3>
            <?php if (empty($commandes)): ?>
                <p>Aucune commande.</p>
            <?php else: ?>
            <table>
                <thead>
                    <tr><th>N°</th><th>Date</th><th>Total</th><th>Statut</th></tr>
                </thead>
                <tbody>
                <?php foreach ($commandes as $cmd): ?>
                    <tr>
                        <td>#<?= $cmd['id'] ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($cmd['date_commande'])) ?></td>
                        <td><?= number_format($cmd['total'], 2) ?> €</td>
                        <td><?= $statut_labels[$cmd['statut']] ?? $cmd['statut'] ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </section>
    </div>
</main>
<footer>
    <p>&copy; 2025-2026 Projet Pizza Nova -préING2- Ibrahim, Ikram &amp; Matthieu</p>
</footer>
</body>
</html>
