<?php
require_once(__DIR__ . '/includes/session.php');
require_once(__DIR__ . '/includes/donnees.php');

if (est_connecte()) { header('Location: profil.php'); exit; }

$erreur  = '';
$succes  = '';
$donnees = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $donnees = [
        'nom'     => trim($_POST['nom'] ?? ''),
        'prenom'  => trim($_POST['prenom'] ?? ''),
        'email'   => trim($_POST['email'] ?? ''),
        'mdp'     => $_POST['mdp'] ?? '',
        'mdp2'    => $_POST['mdp_confirm'] ?? '',
        'tel'     => trim($_POST['tel'] ?? ''),
        'adresse' => trim($_POST['adresse'] ?? ''),
        'infos'   => trim($_POST['infos'] ?? ''),
    ];

    if (empty($donnees['nom']) || empty($donnees['prenom']) || empty($donnees['email']) || empty($donnees['mdp'])) {
        $erreur = 'Veuillez remplir tous les champs obligatoires.';
    } elseif ($donnees['mdp'] !== $donnees['mdp2']) {
        $erreur = 'Les mots de passe ne correspondent pas.';
    } elseif (strlen($donnees['mdp']) < 6) {
        $erreur = 'Le mot de passe doit contenir au moins 6 caractères.';
    } elseif (!filter_var($donnees['email'], FILTER_VALIDATE_EMAIL)) {
        $erreur = 'Adresse email invalide.';
    } elseif (login_existe($donnees['email'])) {
        $erreur = 'Cette adresse email est déjà utilisée.';
    } else {
        $nouvel_utilisateur = [
            'login'               => $donnees['email'],
            'mot_de_passe_clair'  => $donnees['mdp'],
            'mot_de_passe'        => password_hash($donnees['mdp'], PASSWORD_DEFAULT),
            'role'                => 'client',
            'nom'                 => $donnees['nom'],
            'prenom'              => $donnees['prenom'],
            'telephone'           => $donnees['tel'],
            'adresse'             => $donnees['adresse'],
            'details'             => $donnees['infos'],
            'date_inscription'    => date('Y-m-d'),
            'derniere_connexion'  => date('Y-m-d H:i:s'),
            'statut'              => 'actif',
            'points_fidelite'     => 0,
            'remise'              => 0,
        ];
        ajouter_utilisateur($nouvel_utilisateur);
        $succes = 'Compte créé avec succès ! Vous pouvez maintenant vous connecter.';
        $donnees = [];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription - Pizza Nova</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php $base = '';
require_once(__DIR__ . '/includes/nav.php'); ?>
<main>
    <section class="form-container">
        <h2>Rejoignez la famille Pizza Nova</h2>

        <?php if ($erreur): ?>
            <p class="message-erreur"><?= htmlspecialchars($erreur) ?></p>
        <?php endif; ?>
        <?php if ($succes): ?>
            <p class="message-succes"><?= htmlspecialchars($succes) ?> <a href="connexion.php">Se connecter</a></p>
        <?php endif; ?>

        <form action="inscription.php" method="post">
            <div class="form-group">
                <label for="nom">Nom *</label>
                <input type="text" id="nom" name="nom" required value="<?= htmlspecialchars($donnees['nom'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="prenom">Prénom *</label>
                <input type="text" id="prenom" name="prenom" required value="<?= htmlspecialchars($donnees['prenom'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" required value="<?= htmlspecialchars($donnees['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="mdp">Mot de passe * (6 caractères min.)</label>
                <input type="password" id="mdp" name="mdp" required>
            </div>
            <div class="form-group">
                <label for="mdp_confirm">Confirmer le mot de passe *</label>
                <input type="password" id="mdp_confirm" name="mdp_confirm" required>
            </div>
            <div class="form-group">
                <label for="tel">Téléphone</label>
                <input type="tel" id="tel" name="tel" placeholder="06XXXXXXXX" value="<?= htmlspecialchars($donnees['tel'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="adresse">Adresse de livraison</label>
                <textarea id="adresse" name="adresse" rows="3"><?= htmlspecialchars($donnees['adresse'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label for="infos">Infos complémentaires (Interphone, étage...)</label>
                <input type="text" id="infos" name="infos" value="<?= htmlspecialchars($donnees['infos'] ?? '') ?>">
            </div>
            <button type="submit" class="btn-main">Créer mon compte</button>
        </form>
        <p>Déjà un compte ? <a href="connexion.php">Connectez-vous</a></p>
    </section>
</main>
<footer>
    <p>&copy; 2025-2026 Projet Pizza Nova -préING2- Ibrahim, Ikram &amp; Matthieu</p>
</footer>
</body>
</html>
