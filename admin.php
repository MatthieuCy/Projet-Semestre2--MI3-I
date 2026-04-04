<?php
require_once(__DIR__ . '/includes/session.php');
require_once(__DIR__ . '/includes/donnees.php');
exiger_role('admin');

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $uid    = (int)($_POST['user_id'] ?? 0);
    $cible  = get_utilisateur_par_id($uid);

    if ($cible) {
        if ($action === 'bloquer') {
            $cible['statut'] = 'bloque';
            sauvegarder_utilisateur($cible);
            $message = "Compte de {$cible['prenom']} {$cible['nom']} bloqué.";
        } elseif ($action === 'activer') {
            $cible['statut'] = 'actif';
            sauvegarder_utilisateur($cible);
            $message = "Compte de {$cible['prenom']} {$cible['nom']} activé.";
        } elseif ($action === 'changer_statut') {
            $nouveau_statut = $_POST['nouveau_statut'] ?? 'actif';
            $cible['statut'] = $nouveau_statut;
            sauvegarder_utilisateur($cible);
            $message = "Statut de {$cible['prenom']} {$cible['nom']} changé en « {$nouveau_statut} ».";
        } elseif ($action === 'changer_remise') {
            $remise = (int)($_POST['remise'] ?? 0);
            $cible['remise'] = max(0, min(50, $remise));
            sauvegarder_utilisateur($cible);
            $message = "Remise de {$cible['prenom']} {$cible['nom']} définie à {$cible['remise']}%.";
        } elseif ($action === 'supprimer') {
            // On ne peut pas supprimer son propre compte
            $connecte = get_utilisateur_connecte();
            if ($cible['id'] !== $connecte['id']) {
                $tous = get_tous_utilisateurs();
                $tous = array_values(array_filter($tous, fn($u) => $u['id'] !== $cible['id']));
                ecrire_json('utilisateurs.json', $tous);
                $message = "Compte de {$cible['prenom']} {$cible['nom']} supprimé.";
            } else {
                $message = "Impossible de supprimer votre propre compte.";
            }
        }
    }
}

$tous_utilisateurs = get_tous_utilisateurs();
$filtre = $_GET['filtre'] ?? 'tous';

if ($filtre === 'clients') {
    $tous_utilisateurs = array_filter($tous_utilisateurs, fn($u) => $u['role'] === 'client');
} elseif ($filtre === 'staff') {
    $tous_utilisateurs = array_filter($tous_utilisateurs, fn($u) => in_array($u['role'], ['admin','restaurateur','livreur']));
} elseif ($filtre === 'bloques') {
    $tous_utilisateurs = array_filter($tous_utilisateurs, fn($u) => $u['statut'] === 'bloque');
}

$statut_options = ['actif', 'premium', 'vip', 'bloque'];

// Statistiques rapides
$tous = get_tous_utilisateurs();
$nb_total     = count($tous);
$nb_clients   = count(array_filter($tous, fn($u) => $u['role'] === 'client'));
$nb_bloques   = count(array_filter($tous, fn($u) => $u['statut'] === 'bloque'));
$nb_commandes = count(get_toutes_commandes());
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - Pizza Nova</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body>
<?php $base = ''; require_once(__DIR__ . '/includes/nav.php'); ?>

<main class="container">
    <h1>Panneau d'Administration</h1>

    <?php if ($message): ?>
        <p class="message-succes"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <!-- Statistiques -->
    <div class="admin-stats">
        <div class="stat-card">
            <span class="stat-number"><?= $nb_total ?></span>
            <span class="stat-label">Utilisateurs</span>
        </div>
        <div class="stat-card">
            <span class="stat-number"><?= $nb_clients ?></span>
            <span class="stat-label">Clients</span>
        </div>
        <div class="stat-card">
            <span class="stat-number"><?= $nb_commandes ?></span>
            <span class="stat-label">Commandes</span>
        </div>
        <div class="stat-card stat-alert">
            <span class="stat-number"><?= $nb_bloques ?></span>
            <span class="stat-label">Bloqués</span>
        </div>
    </div>

    <!-- Filtres -->
    <div class="admin-filters">
        <a href="admin.php?filtre=tous"    class="filter-btn <?= $filtre === 'tous'    ? 'active' : '' ?>">Tous (<?= count(get_tous_utilisateurs()) ?>)</a>
        <a href="admin.php?filtre=clients" class="filter-btn <?= $filtre === 'clients' ? 'active' : '' ?>">Clients</a>
        <a href="admin.php?filtre=staff"   class="filter-btn <?= $filtre === 'staff'   ? 'active' : '' ?>">Staff</a>
        <a href="admin.php?filtre=bloques" class="filter-btn <?= $filtre === 'bloques' ? 'active' : '' ?>">Bloqués</a>
    </div>

    <!-- Tableau -->
    <div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom / Prénom</th>
                <th>Email</th>
                <th>Rôle</th>
                <th>Statut</th>
                <th>Remise</th>
                <th>Points</th>
                <th>Inscription</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($tous_utilisateurs as $u): ?>
            <tr class="<?= $u['statut'] === 'bloque' ? 'row-bloque' : '' ?>">
                <td><?= $u['id'] ?></td>
                <td><strong><?= htmlspecialchars($u['prenom'] . ' ' . $u['nom']) ?></strong></td>
                <td><?= htmlspecialchars($u['login']) ?></td>
                <td><span class="badge-role badge-role-<?= $u['role'] ?>"><?= ucfirst($u['role']) ?></span></td>
                <td>
                    <form method="post" action="admin.php" style="margin:0;">
                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                        <input type="hidden" name="action" value="changer_statut">
                        <select name="nouveau_statut" onchange="this.form.submit()" class="select-statut select-statut-<?= $u['statut'] ?>">
                            <?php foreach ($statut_options as $opt): ?>
                                <option value="<?= $opt ?>" <?= $u['statut'] === $opt ? 'selected' : '' ?>><?= ucfirst($opt) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </td>
                <td>
                    <form method="post" action="admin.php" class="form-inline">
                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                        <input type="hidden" name="action" value="changer_remise">
                        <input type="number" name="remise" value="<?= $u['remise'] ?? 0 ?>" min="0" max="50" class="input-remise">
                        <button type="submit" class="btn-ok">%</button>
                    </form>
                </td>
                <td><?= $u['points_fidelite'] ?? 0 ?> pts</td>
                <td><?= htmlspecialchars($u['date_inscription']) ?></td>
                <td class="td-actions">
                    <?php if ($u['statut'] !== 'bloque'): ?>
                        <form method="post" action="admin.php" style="display:inline;">
                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                            <input type="hidden" name="action" value="bloquer">
                            <button type="submit" class="btn-ok btn-danger">Bloquer</button>
                        </form>
                    <?php else: ?>
                        <form method="post" action="admin.php" style="display:inline;">
                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                            <input type="hidden" name="action" value="activer">
                            <button type="submit" class="btn-ok btn-success">Débloquer</button>
                        </form>
                    <?php endif; ?>
                    <?php $moi = get_utilisateur_connecte(); if ($u['id'] !== $moi['id']): ?>
                        <form method="post" action="admin.php" style="display:inline;" onsubmit="return confirm('Supprimer ce compte ?')">
                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                            <input type="hidden" name="action" value="supprimer">
                            <button type="submit" class="btn-ok btn-danger">✕</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</main>

<footer>
    <p>&copy; 2025-2026 Projet Pizza Nova -préING2- Ibrahim, Ikram &amp; Matthieu</p>
</footer>
</body>
</html>


