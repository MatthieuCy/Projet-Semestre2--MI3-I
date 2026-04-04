<?php
require_once(__DIR__ . '/includes/session.php');
require_once(__DIR__ . '/includes/donnees.php');
exiger_role('admin');

$message = '';

// Actions sur un utilisateur
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
        }
    }
}

$tous_utilisateurs = get_tous_utilisateurs();
$filtre = $_GET['filtre'] ?? 'tous';
if ($filtre === 'commandes') {
    $ids_avec_commandes = array_unique(array_column(get_toutes_commandes(), 'client_id'));
    $tous_utilisateurs  = array_filter($tous_utilisateurs, fn($u) => in_array($u['id'], $ids_avec_commandes));
}

$statut_options = ['actif', 'premium', 'vip', 'bloque'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Administration - Pizza Nova</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
<?php $base = '../';
require_once '../includes/nav.php'; ?>
<main class="container">
    <h1>Panneau d'Administration</h1>

    <?php if ($message): ?>
        <p class="message-succes"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <div style="margin-bottom:20px; display:flex; gap:10px; flex-wrap:wrap;">
        <a href="admin.php?filtre=tous" class="filter-btn <?= $filtre === 'tous' ? 'active' : '' ?>">Tous les utilisateurs</a>
        <a href="admin.php?filtre=commandes" class="filter-btn <?= $filtre === 'commandes' ? 'active' : '' ?>">Avec commandes</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom / Prénom</th>
                <th>Email</th>
                <th>Rôle</th>
                <th>Statut</th>
                <th>Remise</th>
                <th>Inscription</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($tous_utilisateurs as $u): ?>
            <tr>
                <td><?= $u['id'] ?></td>
                <td><?= htmlspecialchars($u['prenom'] . ' ' . $u['nom']) ?></td>
                <td><?= htmlspecialchars($u['login']) ?></td>
                <td><?= htmlspecialchars($u['role']) ?></td>
                <td>
                    <form method="post" action="admin.php" style="display:inline;">
                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                        <input type="hidden" name="action" value="changer_statut">
                        <select name="nouveau_statut" onchange="this.form.submit()" class="select-statut">
                            <?php foreach ($statut_options as $opt): ?>
                                <option value="<?= $opt ?>" <?= $u['statut'] === $opt ? 'selected' : '' ?>><?= ucfirst($opt) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </td>
                <td>
                    <form method="post" action="admin.php" style="display:flex; gap:5px; align-items:center;">
                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                        <input type="hidden" name="action" value="changer_remise">
                        <input type="number" name="remise" value="<?= $u['remise'] ?>" min="0" max="50" style="width:60px; padding:5px;">
                        <button type="submit" class="btn-ok">%</button>
                    </form>
                </td>
                <td><?= htmlspecialchars($u['date_inscription']) ?></td>
                <td>
                    <a href="admin_profil.php?id=<?= $u['id'] ?>" class="btn-ok">Voir</a>
                    <?php if ($u['statut'] !== 'bloque'): ?>
                        <form method="post" action="admin.php" style="display:inline;">
                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                            <input type="hidden" name="action" value="bloquer">
                            <button type="submit" class="btn-ok" style="background:#c0392b;">Bloquer</button>
                        </form>
                    <?php else: ?>
                        <form method="post" action="admin.php" style="display:inline;">
                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                            <input type="hidden" name="action" value="activer">
                            <button type="submit" class="btn-ok" style="background:#27ae60;">Débloquer</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</main>
<footer>
    <p>&copy; 2025-2026 Projet Pizza Nova -préING2- Ibrahim, Ikram &amp; Matthieu</p>
</footer>
</body>
</html>
