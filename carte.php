<?php
require_once(__DIR__ . '/includes/session.php');
require_once(__DIR__ . '/includes/donnees.php');


$tous_plats = get_tous_plats();
$categorie  = $_GET['cat'] ?? 'toutes';
$filtre_reg = $_GET['regime'] ?? '';

// Filtrage
$plats_affiches = $tous_plats;
if ($categorie !== 'toutes') {
    $plats_affiches = array_filter($plats_affiches, fn($p) => $p['categorie'] === $categorie);
}
if ($filtre_reg === 'sans_gluten') {
    $plats_affiches = array_filter($plats_affiches, fn($p) => $p['sans_gluten'] === true);
}
if ($filtre_reg === 'sans_lactose') {
    $plats_affiches = array_filter($plats_affiches, fn($p) => $p['sans_lactose'] === true);
}

$categories = [
    'toutes'  => 'Toutes',
    'pizza'   => '🍕 Pizzas',
    'entree'  => '🥗 Entrées',
    'dessert' => '🍮 Desserts',
    'boisson' => '🥤 Boissons',
];

// Menus
$menus = get_tous_menus();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>La Carte - Pizza Nova</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body>
<?php $base = '';
require_once(__DIR__ . '/includes/nav.php'); ?>
<main class="container">
    <section class="menu-header">
        <h1>Notre Carte Artisanale</h1>
    </section>

    <!-- Filtres -->
    <section class="filters-bar">
        <div class="filter-group">
            <span>Catégories</span>
            <div class="filter-buttons">
                <?php foreach ($categories as $key => $label): ?>
                    <a href="carte.php?cat=<?= $key ?>&regime=<?= urlencode($filtre_reg) ?>"
                       class="filter-btn <?= $categorie === $key ? 'active' : '' ?>">
                        <?= $label ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="filter-group">
            <span>Régime</span>
            <div class="filter-buttons">
                <a href="carte.php?cat=<?= $categorie ?>&regime=sans_gluten"
                   class="filter-btn <?= $filtre_reg === 'sans_gluten' ? 'active' : '' ?>">Sans Gluten</a>
                <a href="carte.php?cat=<?= $categorie ?>&regime=sans_lactose"
                   class="filter-btn <?= $filtre_reg === 'sans_lactose' ? 'active' : '' ?>">Sans Lactose</a>
                <?php if ($filtre_reg): ?>
                    <a href="carte.php?cat=<?= $categorie ?>" class="filter-btn">✕ Effacer</a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Grille des plats -->
    <?php if (empty($plats_affiches)): ?>
        <p style="text-align:center; padding:40px;">Aucun plat pour ces critères.</p>
    <?php else: ?>
    <div class="grid-pizzas">
        <?php foreach ($plats_affiches as $plat): ?>
        <article class="pizza-card">
            <img src="<?= htmlspecialchars($plat['image']) ?>" alt="<?= htmlspecialchars($plat['nom']) ?>" class="pizza-img"
                 onerror="this.src='images/margherita.jpg'">
            <div class="pizza-info">
                <h3><?= htmlspecialchars($plat['nom']) ?></h3>
                <p><?= htmlspecialchars($plat['description']) ?></p>
                <?php if (!empty($plat['allergenes'])): ?>
                    <p class="allergenes">⚠️ <?= implode(', ', $plat['allergenes']) ?></p>
                <?php endif; ?>
                <span class="price"><?= number_format($plat['prix'], 2) ?> €</span>
                <?php if (get_role_connecte() === 'client' || !est_connecte()): ?>
                    <a href="panier.php?action=ajouter&type=plat&id=<?= $plat['id'] ?>"
                       class="btn-add">Ajouter au panier 🛒</a>
                <?php endif; ?>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Section Menus -->
    <?php if ($categorie === 'toutes'): ?>
    <section class="menu-section">
        <h2>Nos Menus</h2>
        <div class="grid-pizzas">
            <?php foreach ($menus as $menu): ?>
            <article class="pizza-card">
                <img src="<?= htmlspecialchars($menu['image']) ?>" alt="<?= htmlspecialchars($menu['nom']) ?>" class="pizza-img"
                     onerror="this.src='images/margherita.jpg'">
                <div class="pizza-info">
                    <h3><?= htmlspecialchars($menu['nom']) ?></h3>
                    <p><?= htmlspecialchars($menu['description']) ?></p>
                    <?php if ($menu['creneaux'] === 'midi'): ?>
                        <p class="allergenes">🕐 Disponible midi uniquement</p>
                    <?php endif; ?>
                    <?php if ($menu['personnes_min'] > 1): ?>
                        <p class="allergenes">👥 Min. <?= $menu['personnes_min'] ?> personnes</p>
                    <?php endif; ?>
                    <span class="price"><?= number_format($menu['prix_total'], 2) ?> €</span>
                    <?php if (get_role_connecte() === 'client' || !est_connecte()): ?>
                        <a href="panier.php?action=ajouter&type=menu&id=<?= $menu['id'] ?>"
                           class="btn-add">Ajouter au panier 🛒</a>
                    <?php endif; ?>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
</main>
<footer>
    <p>&copy; 2025-2026 Projet Pizza Nova -préING2- Ibrahim, Ikram &amp; Matthieu</p>
</footer>
</body>
</html>
