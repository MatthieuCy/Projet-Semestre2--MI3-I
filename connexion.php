<?php
require_once '../includes/session.php';
require_once '../includes/donnees.php';

// Si déjà connecté, rediriger
if (est_connecte()) {
    $role = get_role_connecte();
    if ($role === 'admin') header('Location: admin.php');
    elseif ($role === 'restaurateur') header('Location: restaurateur.php');
    elseif ($role === 'livreur') header('Location: livraison.php');
    else header('Location: profil.php');
    exit;
}

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['email'] ?? '');
    $mdp   = trim($_POST['mdp'] ?? '');

    if (empty($login) || empty($mdp)) {
        $erreur = 'Veuillez remplir tous les champs.';
    } else {
        $utilisateur = get_utilisateur_par_login($login);
        // Vérification du mot de passe (stocké en clair pour la démo / hash en prod)
        if ($utilisateur && ($mdp === $utilisateur['mot_de_passe_clair'])) {
            if ($utilisateur['statut'] === 'bloque') {
                $erreur = 'Ce compte est désactivé. Contactez l\'administrateur.';
            } else {
                // Mettre à jour la dernière connexion
                $utilisateur['derniere_connexion'] = date('Y-m-d H:i:s');
                sauvegarder_utilisateur($utilisateur);

                connecter_utilisateur($utilisateur);

                // Redirection selon le rôle
                switch ($utilisateur['role']) {
                    case 'admin':        header('Location: admin.php'); break;
                    case 'restaurateur': header('Location: restaurateur.php'); break;
                    case 'livreur':      header('Location: livraison.php'); break;
                    default:             header('Location: profil.php'); break;
                }
                exit;
            }
        } else {
            $erreur = 'Email ou mot de passe incorrect.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion - Pizza Nova</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
<?php $base = '';
require_once '../includes/nav.php'; ?>
<main>
    <section class="form-container">
        <h2>Connexion à votre espace</h2>

        <?php if ($erreur): ?>
            <p class="message-erreur"><?= htmlspecialchars($erreur) ?></p>
        <?php endif; ?>

        <form action="connexion.php" method="post">
            <div class="form-group">
                <label for="email">Adresse Email :</label>
                <input type="email" id="email" name="email" required
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="mdp">Mot de passe :</label>
                <input type="password" id="mdp" name="mdp" required>
            </div>
            <button type="submit" class="btn-main">Se connecter</button>
        </form>

        <div class="aide-connexion">
            <p>Comptes de test :</p>
            <ul>
                <li>Client : <strong>client1@pizza.fr</strong> / password</li>
                <li>Admin : <strong>admin@pizza.fr</strong> / password</li>
                <li>Cuisine : <strong>cuisinier@pizza.fr</strong> / password</li>
                <li>Livreur : <strong>livreur@pizza.fr</strong> / password</li>
            </ul>
        </div>

        <p>Pas encore de compte ? <a href="inscription.php">Inscrivez-vous ici</a></p>
    </section>
</main>
<footer>
    <p>&copy; 2025-2026 Projet Pizza Nova -préING2- Ibrahim, Ikram &amp; Matthieu</p>
</footer>
</body>
</html>
