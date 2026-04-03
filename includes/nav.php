<?php
// includes/nav.php
require_once __DIR__ . '/session.php';
$u    = get_utilisateur_connecte();
$role = $u ? $u['role'] : null;
?>
<header>
    <nav>
        <div class="logo">🍕 Pizza Nova</div>
        <ul>
            <li><a href="index.php">Accueil</a></li>
            <li><a href="carte.php">La Carte</a></li>
            <li><a href="identite.php">Qui sommes-nous</a></li>
            <?php if (!$u): ?>
                <li><a href="inscription.php">S'inscrire</a></li>
                <li><a href="connexion.php">Connexion</a></li>
            <?php else: ?>
                <li><a href="profil.php">Mon Profil (<?= htmlspecialchars($u['prenom']) ?>)</a></li>
                <?php if ($role === 'admin'): ?>
                    <li><a href="admin.php">Admin</a></li>
                <?php endif; ?>
                <?php if ($role === 'restaurateur' || $role === 'admin'): ?>
                    <li><a href="restaurateur.php">Cuisine</a></li>
                <?php endif; ?>
                <?php if ($role === 'livreur' || $role === 'admin'): ?>
                    <li><a href="livraison.php">Livreur</a></li>
                <?php endif; ?>
                <?php if ($role === 'client'): ?>
                    <li><a href="panier.php">🛒 Panier
                        <?php
                        $panier = $_SESSION['panier'] ?? [];
                        $nb = array_sum(array_column($panier, 'quantite'));
                        if ($nb > 0) echo "<span class=\"badge-panier\">$nb</span>";
                        ?>
                    </a></li>
                <?php endif; ?>
                <li><a href="deconnexion.php">Déconnexion</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>
