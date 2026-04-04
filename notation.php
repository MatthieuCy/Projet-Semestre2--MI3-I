<?php
require_once '../includes/session.php';
require_once '../includes/donnees.php';
exiger_connexion();

$u           = get_utilisateur_connecte();
$commande_id = (int)($_GET['commande_id'] ?? 0);
$commande    = $commande_id ? get_commande_par_id($commande_id) : null;
$erreur      = '';
$succes      = '';

// Vérifier que la commande appartient au client
if ($commande && $commande['client_id'] !== $u['id']) {
    header('Location: profil.php'); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cid           = (int)($_POST['commande_id'] ?? 0);
    $note_produits = (int)($_POST['food'] ?? 0);
    $note_livraison= (int)($_POST['delivery'] ?? 0);
    $commentaire   = trim($_POST['comment'] ?? '');
    $commande_a_noter = get_commande_par_id($cid);

    if (!$commande_a_noter || $commande_a_noter['client_id'] !== $u['id']) {
        $erreur = 'Commande introuvable.';
    } elseif ($commande_a_noter['statut'] !== 'livree') {
        $erreur = 'Cette commande n\'a pas encore été livrée.';
    } elseif ($commande_a_noter['note_produits'] !== null) {
        $erreur = 'Cette commande a déjà été notée.';
    } elseif ($note_produits < 1 || $note_produits > 5 || $note_livraison < 1 || $note_livraison > 5) {
        $erreur = 'Veuillez donner une note entre 1 et 5 pour chaque critère.';
    } else {
        $commande_a_noter['note_produits']  = $note_produits;
        $commande_a_noter['note_livraison'] = $note_livraison;
        $commande_a_noter['commentaire']    = $commentaire;
        sauvegarder_commande($commande_a_noter);
        // +5 points bonus pour avoir noté
        $u['points_fidelite'] += 5;
        sauvegarder_utilisateur($u);
        $succes = 'Merci pour votre avis ! Vous avez gagné 5 points fidélité.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Noter ma commande - Pizza Nova</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php $base = '../';
require_once '../includes/nav.php'; ?>
<main class="container">
    <h2>Votre avis nous intéresse !</h2>
    <h3>Merci d'avoir commandé chez Pizza Nova.</h3>

    <?php if ($erreur): ?>
        <p class="message-erreur"><?= htmlspecialchars($erreur) ?></p>
    <?php endif; ?>
    <?php if ($succes): ?>
        <p class="message-succes"><?= htmlspecialchars($succes) ?> <a href="profil.php">Voir mon profil</a></p>
    <?php else: ?>
    <form action="notation.php" method="POST" class="rating-form">
        <input type="hidden" name="commande_id" value="<?= $commande_id ?: 0 ?>">

        <fieldset>
            <legend>Qualité des produits</legend>
            <div class="rating-options">
                <?php for ($i = 5; $i >= 1; $i--): ?>
                    <input type="radio" name="food" id="f<?= $i ?>" value="<?= $i ?>">
                    <label for="f<?= $i ?>">⭐ <?= $i ?></label>
                <?php endfor; ?>
            </div>
        </fieldset>

        <fieldset>
            <legend>Qualité de la livraison</legend>
            <div class="rating-options">
                <?php for ($i = 5; $i >= 1; $i--): ?>
                    <input type="radio" name="delivery" id="d<?= $i ?>" value="<?= $i ?>">
                    <label for="d<?= $i ?>">⭐ <?= $i ?></label>
                <?php endfor; ?>
            </div>
        </fieldset>

        <div class="form-group">
            <label for="comment">Un mot pour nous aider à nous améliorer ?</label>
            <textarea id="comment" name="comment" rows="4" placeholder="Votre message ici..."></textarea>
        </div>
        <button type="submit" class="btn-main">Envoyer ma note</button>
    </form>
    <?php endif; ?>
</main>
<footer>
    <p>&copy; 2025-2026 Projet Pizza Nova -préING2- Ibrahim, Ikram &amp; Matthieu</p>
</footer>
</body>
</html>
