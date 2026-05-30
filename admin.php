<?php
require_once(__DIR__ . '/includes/session.php');
require_once(__DIR__ . '/includes/donnees.php');
require_once(__DIR__ . '/includes/logs.php');
$logs = lire_logs();
exiger_role('admin');

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $uid    = (int)($_POST['user_id'] ?? 0);
    $cible  = get_utilisateur_par_id($uid);

    if ($cible) {
        if ($action === 'changer_statut') {
            $cible['statut'] = $_POST['nouveau_statut'] ?? 'actif';
            sauvegarder_utilisateur($cible);
            $message = "Statut de {$cible['prenom']} {$cible['nom']} changé.";
        } elseif ($action === 'changer_remise') {
            $cible['remise'] = max(0, min(50, (int)($_POST['remise'] ?? 0)));
            sauvegarder_utilisateur($cible);
            $message = "Remise de {$cible['prenom']} {$cible['nom']} définie à {$cible['remise']}%.";
        } elseif ($action === 'supprimer') {
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
if ($filtre === 'clients') $tous_utilisateurs = array_filter($tous_utilisateurs, fn($u) => $u['role'] === 'client');
elseif ($filtre === 'staff')   $tous_utilisateurs = array_filter($tous_utilisateurs, fn($u) => in_array($u['role'], ['admin','restaurateur','livreur']));
elseif ($filtre === 'bloques') $tous_utilisateurs = array_filter($tous_utilisateurs, fn($u) => $u['statut'] === 'bloque');

$statut_options = ['actif', 'premium', 'vip', 'bloque'];
$tous      = get_tous_utilisateurs();
$nb_total  = count($tous);
$nb_clients  = count(array_filter($tous, fn($u) => $u['role'] === 'client'));
$nb_bloques  = count(array_filter($tous, fn($u) => $u['statut'] === 'bloque'));
$nb_commandes = count(get_toutes_commandes());
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - Pizza Nova</title>
</head>
<body>
<?php $base = ''; require_once(__DIR__ . '/includes/nav.php'); ?>
<main class="container">
    <h1>Panneau d'Administration</h1>

    <div id="message-admin">
    <?php if ($message): ?>
        <p class="message-succes"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>
    </div>

    <!-- Stats -->
    <div class="admin-stats">
        <div class="stat-card"><span class="stat-number"><?= $nb_total ?></span><span class="stat-label">Utilisateurs</span></div>
        <div class="stat-card"><span class="stat-number"><?= $nb_clients ?></span><span class="stat-label">Clients</span></div>
        <div class="stat-card"><span class="stat-number"><?= $nb_commandes ?></span><span class="stat-label">Commandes</span></div>
        <div class="stat-card stat-alert"><span class="stat-number"><?= $nb_bloques ?></span><span class="stat-label">Bloqués</span></div>
    </div>

    <!-- Derniers avis -->
    <div class="admin-filters"><h2>Derniers avis reçus</h2></div>
    <div class="table-wrapper">
        <table>
            <thead><tr><th>Commande</th><th>Client</th><th>Note Produits</th><th>Note Livraison</th><th>Commentaire</th></tr></thead>
            <tbody>
            <?php foreach (get_toutes_commandes() as $c):
                if (isset($c['note_produits']) && $c['note_produits'] !== null):
                    $client_avis = get_utilisateur_par_id($c['client_id']); ?>
                <tr>
                    <td>#<?= $c['id'] ?></td>
                    <td><?= htmlspecialchars($client_avis['prenom'] ?? 'Inconnu') ?></td>
                    <td><?= str_repeat('⭐', $c['note_produits']) ?></td>
                    <td><?= str_repeat('⭐', $c['note_livraison']) ?></td>
                    <td><em><?= htmlspecialchars($c['commentaire'] ?? '') ?></em></td>
                </tr>
            <?php endif; endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Filtres -->
    <div class="admin-filters">
        <a href="admin.php?filtre=tous"    class="filter-btn <?= $filtre==='tous'    ? 'active':'' ?>">Tous (<?= count(get_tous_utilisateurs()) ?>)</a>
        <a href="admin.php?filtre=clients" class="filter-btn <?= $filtre==='clients' ? 'active':'' ?>">Clients</a>
        <a href="admin.php?filtre=staff"   class="filter-btn <?= $filtre==='staff'   ? 'active':'' ?>">Staff</a>
        <a href="admin.php?filtre=bloques" class="filter-btn <?= $filtre==='bloques' ? 'active':'' ?>">Bloqués</a>
    </div>

    <!-- Tableau utilisateurs -->
    <div class="table-wrapper">
    <table id="table-utilisateurs">
        <thead>
            <tr><th>ID</th><th>Nom / Prénom</th><th>Email</th><th>Rôle</th><th>Statut</th><th>Remise</th><th>Points</th><th>Inscription</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php foreach ($tous_utilisateurs as $u): ?>
            <tr id="row-user-<?= $u['id'] ?>" class="<?= $u['statut'] === 'bloque' ? 'row-bloque' : '' ?>">
                <td><?= $u['id'] ?></td>
                <td><strong><?= htmlspecialchars($u['prenom'] . ' ' . $u['nom']) ?></strong></td>
                <td><?= htmlspecialchars($u['login']) ?></td>
                <td><span class="badge-role badge-role-<?= $u['role'] ?>"><?= ucfirst($u['role']) ?></span></td>
                <td>
                    <form method="post" action="admin.php" class="form-inline">
                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                        <input type="hidden" name="action" value="changer_statut">
                        <select name="nouveau_statut" onchange="this.form.submit()" class="select-statut select-statut-<?= $u['statut'] ?>">
                            <?php foreach ($statut_options as $opt): ?>
                                <option value="<?= $opt ?>" <?= $u['statut']===$opt ? 'selected':'' ?>><?= ucfirst($opt) ?></option>
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
                    <a href="admin_profil.php?id=<?= $u['id'] ?>" class="btn-ok">Voir</a>


                     <!-- Tableau Logs -->
<div class="admin-filters"><h2>Logs d'incidents</h2></div>
<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Login</th>
                <th>IP</th>
                <th>Détails</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($logs)): ?>
                <tr><td colspan="5"><em>Aucun incident enregistré.</em></td></tr>
            <?php else: ?>
                <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?= htmlspecialchars($log['date']) ?></td>
                    <td><span class="badge-role"><?= htmlspecialchars($log['type']) ?></span></td>
                    <td><?= htmlspecialchars($log['login']) ?></td>
                    <td><?= htmlspecialchars($log['ip']) ?></td>
                    <td><em><?= htmlspecialchars($log['details']) ?></em></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>


                    

    <!-- Boutons bloquer/débloquer ASYNCHRONES  -->
                    <?php if ($u['statut'] !== 'bloque'): ?>
                        <button class="btn-ok btn-danger btn-bloquer"
                                onclick="actionUtilisateur(<?= $u['id'] ?>, 'bloquer', this)">
                            Bloquer
                        </button>
                    <?php else: ?>
                        <button class="btn-ok btn-success btn-debloquer"
                                onclick="actionUtilisateur(<?= $u['id'] ?>, 'activer', this)">
                            Débloquer
                        </button>
                    <?php endif; ?>

                    <?php $moi = get_utilisateur_connecte(); if ($u['id'] !== $moi['id']): ?>
                        <form method="post" action="admin.php" class="form-inline-block" onsubmit="return confirm('Supprimer ce compte ?')">
                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                            <input type="hidden" name="action" value="supprimer">
                            <button type="submit" class="btn-ok btn-danger btn-bloquer">Supprimer</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</main>

<!-- Script JS pour les actions asynchrones -->
<script>
// Phase 3 — Bloquer/Débloquer un utilisateur en asynchrone (sans rechargement)
function actionUtilisateur(userId, action, bouton) {
    const labels = { bloquer: 'Bloquer', activer: 'Débloquer' };
    if (!confirm(`Voulez-vous ${labels[action].toLowerCase()} cet utilisateur ?`)) return;

    // Désactiver le bouton pendant la requête
    bouton.disabled = true;
    bouton.textContent = '⏳';

    fetch('api_admin.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: action, user_id: userId })
    })
    .then(r => r.json())
    .then(data => {
        if (data.succes) {
            afficherMessageAdmin(data.message, 'succes');

            // Mettre à jour la ligne sans recharger
            const row = document.getElementById('row-user-' + userId);
            if (row) {
                if (data.nouveau_statut === 'bloque') {
                    row.classList.add('row-bloque');
                    // Remplacer le bouton Bloquer par Débloquer
                    bouton.textContent   = 'Débloquer';
                    bouton.style.background = '#27ae60';
                    bouton.setAttribute('onclick', `actionUtilisateur(${userId}, 'activer', this)`);
                } else {
                    row.classList.remove('row-bloque');
                    bouton.textContent   = 'Bloquer';
                    bouton.style.background = '#c0392b';
                    bouton.setAttribute('onclick', `actionUtilisateur(${userId}, 'bloquer', this)`);
                }
            }
        } else {
            afficherMessageAdmin(data.message || 'Erreur.', 'erreur');
        }
        bouton.disabled = false;
    })
    .catch(() => {
        afficherMessageAdmin('Erreur de connexion au serveur.', 'erreur');
        bouton.disabled = false;
        bouton.textContent = labels[action];
    });
}

function afficherMessageAdmin(message, type) {
    const zone = document.getElementById('message-admin');
    zone.innerHTML = `<p class="message-${type}">${message}</p>`;
    // Faire disparaître après 4 secondes
    setTimeout(() => { zone.innerHTML = ''; }, 4000);
}
</script>

<footer><p>&copy; 2025-2026 Projet Pizza Nova -préING2- Ibrahim, Ikram &amp; Matthieu</p></footer>
</body>
</html>
