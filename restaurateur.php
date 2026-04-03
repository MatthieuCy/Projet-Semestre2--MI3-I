<?php
require_once(__DIR__ . '/includes/session.php');
require_once(__DIR__ . '/includes/donnees.php');

exiger_role('restaurateur');

$message_info = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_cmd = (int)$_POST['commande_id'];
    $action = $_POST['action'];
    $commande = get_commande_par_id($id_cmd);

    if ($commande != null) {
        if ($action === 'lancer' && $commande['statut'] === 'en_attente') {
            $commande['statut'] = 'en_preparation';
            sauvegarder_commande($commande);
            $message_info = "Commande #" . $id_cmd . " lancée.";
        } 
        elseif ($action === 'pret' && $commande['statut'] === 'en_preparation') {
            if ($commande['type'] === 'livraison') {
                $id_livreur = (int)$_POST['livreur_id'];
                if ($id_livreur > 0) {
                    $commande['livreur_id'] = $id_livreur;
                    $commande['statut'] = 'en_livraison';
                    sauvegarder_commande($commande);
                    $infos_livreur = get_utilisateur_par_id($id_livreur);
                    $message_info = "Commande confiée à " . htmlspecialchars($infos_livreur['prenom']);
                } else {
                    $message_info = "Erreur : Sélectionnez un livreur.";
                }
            } else {
                $commande['statut'] = 'livree';
                $commande['date_livraison_effective'] = date('Y-m-d H:i:s');
                sauvegarder_commande($commande);
                $message_info = "Commande #" . $id_cmd . " terminée.";
            }
        }
    }
}

$liste_attente     = get_commandes_par_statut('en_attente');
$liste_preparation = get_commandes_par_statut('en_preparation');
$liste_livraison   = get_commandes_par_statut('en_livraison');
$livreurs_libres   = get_livreurs_disponibles();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Interface Cuisine - Pizza Nova</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body>

<?php 
    $base = '../'; 
    require_once(__DIR__ . '/includes/nav.php'); 
?>

<main class="cuisine container">
    <h2>Gestion des Commandes 🍕</h2>

    <?php if ($message_info != "") { ?>
        <p class="message-succes">
            <?php echo htmlspecialchars($message_info); ?>
        </p>
    <?php } ?>

    <div class="kanban">

        <div class="kanban-col">
            <h3 class="kanban-titre attente">⏳ En attente</h3>
            <?php foreach ($liste_attente as $c) { 
                $client = get_utilisateur_par_id($c['client_id']);
            ?>
                <article class="commande apreparer">
                    <h4>Commande #<?php echo $c['id']; ?></h4>
                    <p><strong>Client :</strong> <?php echo htmlspecialchars($client['prenom'] . " " . $client['nom']); ?></p>
                    <ul>
                        <?php foreach ($c['articles'] as $art) { ?>
                            <li><?php echo $art['quantite']; ?>x <?php echo htmlspecialchars($art['nom']); ?></li>
                        <?php } ?>
                    </ul>
                    <form method="post">
                        <input type="hidden" name="commande_id" value="<?php echo $c['id']; ?>">
                        <input type="hidden" name="action" value="lancer">
                        <button type="submit" class="btn-main btn-preparer">Préparer</button>
                    </form>
                </article>
            <?php } ?>
        </div>

        <div class="kanban-col">
            <h3 class="kanban-titre preparation">👨‍🍳 En cuisine</h3>
            <?php foreach ($liste_preparation as $c) { 
                $client = get_utilisateur_par_id($c['client_id']);
            ?>
                <article class="commande en-prep">
                    <h4>Commande #<?php echo $c['id']; ?></h4>
                    <p><strong>Client :</strong> <?php echo htmlspecialchars($client['prenom']); ?></p>
                    <ul>
                        <?php foreach ($c['articles'] as $art) { ?>
                            <li><?php echo $art['quantite']; ?>x <?php echo htmlspecialchars($art['nom']); ?></li>
                        <?php } ?>
                    </ul>
                    <form method="post">
                        <input type="hidden" name="commande_id" value="<?php echo $c['id']; ?>">
                        <input type="hidden" name="action" value="pret">
                        <?php if ($c['type'] === 'livraison') { ?>
                            <select name="livreur_id" required>
                                <option value="">-- Livreur --</option>
                                <?php foreach ($livreurs_libres as $l) { ?>
                                    <option value="<?php echo $l['id']; ?>">
                                        <?php echo htmlspecialchars($l['prenom'] . " " . $l['nom']); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        <?php } ?>
                        <button type="submit" class="btn-main" style="background-color:#27ae60;">Prêt !</button>
                    </form>
                </article>
            <?php } ?>
        </div>

        <div class="kanban-col">
            <h3 class="kanban-titre livraison-col">🛵 En livraison</h3>
            <?php foreach ($liste_livraison as $c) { 
                $livreur = get_utilisateur_par_id($c['livreur_id']);
            ?>
                <article class="commande livraison">
                    <h4>Commande #<?php echo $c['id']; ?></h4>
                    <p><strong>Livreur :</strong> <?php echo htmlspecialchars($livreur['prenom']); ?></p>
                    <p>Heure de commande : <?php echo date('H:i', strtotime($c['date_commande'])); ?></p>
                </article>
            <?php } ?>
        </div>

    </div>
</main>

<footer>
    <p>&copy; 2025-2026 Projet Pizza Nova - Ibrahim, Ikram &amp; Matthieu</p>
</footer>

</body>
</html>                    <li>CHOIX 1</li>
                    <li>CHOIX 2</li>
                    <li>CHOIX 3</li>
                </ul>
                
                <p><strong>Statut :</strong> En cuisine</p>
                
                <button class="btn-main btn-preparer">Marquer comme Prêt</button>
            </article>

            <article class="commande livraison">
                <h3>Commande n°xxx</h3>
                
                <p><strong>Livreur :</strong> Ibrahim N.</p>
                <p><strong>Statut :</strong> En cours de livraison...</p>
                <p><small>Partie de la cuisine il y a 10 min</small></p>
            </article>

            <article class="commande apreparer">
                <h3>Commande n°XXX</h3>
                <ul>
                    <li>CHOIX 1</li>
                    <li>CHOIX 2</li>
                </ul>
                <p><strong>Statut :</strong> En attente</p>
                <button class="btn-main btn-preparer">Lancer la commande</button>
            </article>
            
        </div>
    </main>

    <footer>
        <p>&copy; 2025-2026 Projet Pizza Nova -préING2- Ibrahim, Ikram & Matthieu</p>
    </footer>
    
</body>
</html>
