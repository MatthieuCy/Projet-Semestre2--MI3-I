<?php
require_once(__DIR__ . '/includes/session.php');
require_once(__DIR__ . '/includes/donnees.php');
exiger_role('restaurateur');

$message_info = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_cmd   = (int)$_POST['commande_id'];
    $action   = $_POST['action'];
    $commande = get_commande_par_id($id_cmd);

    if ($commande !== null) {
        if ($action === 'lancer' && $commande['statut'] === 'en_attente') {
            $commande['statut'] = 'en_preparation';
            sauvegarder_commande($commande);
            $message_info = "Commande #" . $id_cmd . " lancée en cuisine.";

        } elseif ($action === 'pret' && $commande['statut'] === 'en_preparation') {
            if ($commande['type'] === 'livraison') {
                // Commande à livrer → affecter un livreur
                $id_livreur = (int)($_POST['livreur_id'] ?? 0);
                if ($id_livreur > 0) {
                    $commande['livreur_id'] = $id_livreur;
                    $commande['statut']     = 'en_livraison';
                    sauvegarder_commande($commande);
                    $infos_livreur = get_utilisateur_par_id($id_livreur);
                    $message_info  = "Commande #" . $id_cmd . " confiée à " . htmlspecialchars($infos_livreur['prenom'] . ' ' . $infos_livreur['nom']) . ".";
                } else {
                    $message_info = "Erreur : veuillez sélectionner un livreur.";
                }
            } else {
                // Sur place ou à emporter → terminée immédiatement
                $commande['statut']                    = 'livree';
                $commande['date_livraison_effective']   = date('Y-m-d H:i:s');
                sauvegarder_commande($commande);
                // +10 points fidélité pour le client
                $client = get_utilisateur_par_id($commande['client_id']);
                if ($client) {
                    $client['points_fidelite'] += 10;
                    sauvegarder_utilisateur($client);
                }
                $label = $commande['type'] === 'emporter' ? 'à emporter' : 'sur place';
                $message_info = "Commande #" . $id_cmd . " ($label) prête et clôturée.";
            }
        }
    }
}

$liste_attente     = array_values(get_commandes_par_statut('en_attente'));
$liste_preparation = array_values(get_commandes_par_statut('en_preparation'));
$liste_livraison   = array_values(get_commandes_par_statut('en_livraison'));
$livreurs_libres   = array_values(get_livreurs_disponibles());

$type_labels = [
    'livraison' => ' Livraison',
    'emporter'  => ' À emporter',
    'sur_place' => ' Sur place',
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interface Cuisine - Pizza Nova</title>
</head>
<body>
<?php $base = ''; require_once(__DIR__ . '/includes/nav.php'); ?>

<main class="container">
    <h1>Gestion des Commandes </h1>

    <?php if ($message_info !== ""): ?>
        <p class="message-succes"><?= htmlspecialchars($message_info) ?></p>
    <?php endif; ?>

    <div class="kanban">

        <!-- COLONNE EN ATTENTE -->
        <div class="kanban-col">
            <h3 class="kanban-titre kanban-attente"> En attente (<?= count($liste_attente) ?>)</h3>
            <?php if (empty($liste_attente)): ?>
                <p class="kanban-vide">Aucune commande en attente.</p>
            <?php endif; ?>
            <?php foreach ($liste_attente as $c):
                $client = get_utilisateur_par_id($c['client_id']);
            ?>
                <article class="commande commande-attente">
                    <div class="commande-header">
                        <h4>Commande #<?= $c['id'] ?></h4>
                        <span class="badge-type"><?= $type_labels[$c['type'] ?? ''] ?? $c['type'] ?></span>
                    </div>
                    <p><strong>Client :</strong> <?= htmlspecialchars($client ? $client['prenom'].' '.$client['nom'] : 'Inconnu') ?></p>
                    <p class="commande-heure"> <?= date('H:i', strtotime($c['date_commande'])) ?></p>
                    <ul class="commande-articles">
                        <?php foreach ($c['articles'] as $art): ?>
                            <li><?= $art['quantite'] ?>× <?= htmlspecialchars($art['nom']) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <p class="commande-total"><strong><?= number_format($c['total'], 2) ?> €</strong></p>
                    <!-- Bouton lancer ASYNCHRONE -->
                    <button class="btn-main btn-preparer"
                            onclick="changerStatut(<?= $c['id'] ?>, 'lancer', this)">
                         Lancer la préparation
                    </button>
                </article>
            <?php endforeach; ?>
        </div>

        <!-- COLONNE EN PRÉPARATION -->
        <div class="kanban-col">
            <h3 class="kanban-titre kanban-preparation"> En cuisine (<?= count($liste_preparation) ?>)</h3>
            <?php if (empty($liste_preparation)): ?>
                <p class="kanban-vide">Aucune commande en préparation.</p>
            <?php endif; ?>
            <?php foreach ($liste_preparation as $c):
                $client = get_utilisateur_par_id($c['client_id']);
            ?>
                <article class="commande commande-preparation">
                    <div class="commande-header">
                        <h4>Commande #<?= $c['id'] ?></h4>
                        <span class="badge-type"><?= $type_labels[$c['type'] ?? ''] ?? $c['type'] ?></span>
                    </div>
                    <p><strong>Client :</strong> <?= htmlspecialchars($client ? $client['prenom'] : 'Inconnu') ?></p>
                    <ul class="commande-articles">
                        <?php foreach ($c['articles'] as $art): ?>
                            <li><?= $art['quantite'] ?>× <?= htmlspecialchars($art['nom']) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <p class="commande-total"><strong><?= number_format($c['total'], 2) ?> €</strong></p>
                    <!-- Bouton prêt ASYNCHRONE -->
                    <?php if ($c['type'] === 'livraison'): ?>
                        <label class="label-select"><strong>Affecter un livreur :</strong></label>
                        <select id="livreur-<?= $c['id'] ?>" class="select-statut">
                            <option value="">-- Choisir un livreur --</option>
                            <?php foreach ($livreurs_libres as $l): ?>
                                <option value="<?= $l['id'] ?>"><?= htmlspecialchars($l['prenom'].' '.$l['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (empty($livreurs_libres)): ?>
                            <p class="message-erreur"> Aucun livreur disponible.</p>
                        <?php endif; ?>
                    <?php endif; ?>
                    <button class="btn-main btn-success"
                            onclick="changerStatut(<?= $c['id'] ?>, 'pret', this, <?= $c['type'] === 'livraison' ? "'livreur-{$c['id']}'" : 'null' ?>)">
                         Prêt !
                    </button>
                </article>
            <?php endforeach; ?>
        </div>

        <!-- COLONNE EN LIVRAISON -->
        <div class="kanban-col">
            <h3 class="kanban-titre kanban-livraison"> En livraison (<?= count($liste_livraison) ?>)</h3>
            <?php if (empty($liste_livraison)): ?>
                <p class="kanban-vide">Aucune commande en livraison.</p>
            <?php endif; ?>
            <?php foreach ($liste_livraison as $c):
                $livreur = get_utilisateur_par_id($c['livreur_id']);
            ?>
                <article class="commande commande-livraison">
                    <div class="commande-header">
                        <h4>Commande #<?= $c['id'] ?></h4>
                        <span class="badge-type"> Livraison</span>
                    </div>
                    <p><strong>Livreur :</strong> <?= htmlspecialchars($livreur ? $livreur['prenom'].' '.$livreur['nom'] : 'Inconnu') ?></p>
                    <p><strong> Adresse :</strong> <?= htmlspecialchars($c['adresse_livraison']) ?></p>
                    <p class="commande-heure"> Parti à <?= date('H:i', strtotime($c['date_commande'])) ?></p>
                </article>
            <?php endforeach; ?>
        </div>

    </div>
</main>

<footer>
    <p>&copy; 2025-2026 Projet Pizza Nova -préING2- Ibrahim, Ikram &amp; Matthieu</p>
</footer>
<!-- Script JS pour les actions asynchrones -->
<script>
function changerStatut(commandeId, action, bouton, selectId = null) {
    const body = { commande_id: commandeId, action: action };

    if (selectId) {
        const livreurId = document.getElementById(selectId)?.value;
        if (!livreurId) {
            alert('Veuillez sélectionner un livreur.');
            return;
        }
        body.livreur_id = parseInt(livreurId);
    }

    bouton.disabled    = true;
    bouton.textContent = '...';

    fetch('api_restaurateur.php', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify(body)
    })
    .then(r => r.json())
    .then(data => {
        if (data.succes) {
            afficherNotification('OK - ' + data.message, 'succes');
            setTimeout(() => location.reload(), 1500);
        } else {
            afficherNotification('Erreur - ' + data.message, 'erreur');
            bouton.disabled    = false;
            bouton.textContent = action === 'lancer' ? 'Lancer la preparation' : 'Pret !';
        }
    })
    .catch(() => {
        afficherNotification('Erreur réseau.', 'erreur');
        bouton.disabled    = false;
    });
}

function afficherNotification(message, type) {
    const notif = document.createElement('div');
    notif.textContent = message;
    notif.style.cssText = `
        position:fixed; top:20px; right:20px; z-index:9999;
        padding:12px 20px; border-radius:8px; font-weight:bold;
        background:${type === 'succes' ? '#d4edda' : '#f8d7da'};
        color:${type === 'succes' ? '#155724' : '#721c24'};
        box-shadow:0 2px 8px rgba(0,0,0,0.2);
    `;
    document.body.appendChild(notif);
    setTimeout(() => notif.remove(), 3000);
}
</script>

</body>
</html>
