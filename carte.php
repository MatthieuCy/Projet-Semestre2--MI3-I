<?php
require_once(__DIR__ . '/includes/session.php');
require_once(__DIR__ . '/includes/donnees.php');

$tous_plats = get_tous_plats();
$categorie  = $_GET['cat']       ?? 'toutes';
$filtre_reg = $_GET['regime']    ?? '';
$recherche  = trim($_GET['recherche'] ?? '');

$plats_affiches = $tous_plats;
if ($categorie !== 'toutes') {
    $plats_affiches = array_filter($plats_affiches, fn($p) => $p['categorie'] === $categorie);
}
if ($filtre_reg === 'sans_gluten')  $plats_affiches = array_filter($plats_affiches, fn($p) => $p['sans_gluten'] === true);
if ($filtre_reg === 'sans_lactose') $plats_affiches = array_filter($plats_affiches, fn($p) => $p['sans_lactose'] === true);
if ($filtre_reg === 'vegetarien')   $plats_affiches = array_filter($plats_affiches, fn($p) => $p['vegetarien'] === true);
if ($filtre_reg === 'vegan')        $plats_affiches = array_filter($plats_affiches, fn($p) => $p['vegan'] === true);
if ($filtre_reg === 'halal')        $plats_affiches = array_filter($plats_affiches, fn($p) => $p['halal'] === true);
if ($recherche !== '') {
    $plats_affiches = array_filter($plats_affiches, fn($p) => stripos($p['nom'], $recherche) !== false || stripos($p['description'], $recherche) !== false);
}

$categories = ['toutes'=>'Toutes','pizza'=>' Pizzas','entree'=>' Entrées','dessert'=>' Desserts','boisson'=>' Boissons'];
$menus = get_tous_menus();

// Comptage des commandes par plat pour le tri "populaire"
$comptage_plats = [];
foreach (get_toutes_commandes() as $cmd) {
    foreach ($cmd['articles'] ?? [] as $art) {
        if ($art['type'] === 'plat') {
            $comptage_plats[$art['id']] = ($comptage_plats[$art['id']] ?? 0) + ($art['quantite'] ?? 1);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>La Carte - Pizza Nova</title>
</head>
<body>
<?php $base = ''; require_once(__DIR__ . '/includes/nav.php'); ?>
<main class="container">
    <section class="menu-header">
        <h1>Notre Carte Artisanale</h1>
    </section>

    <section class="filters-bar">
        <div class="filter-group">
            <span>Catégories</span>
            <div class="filter-buttons">
                <?php foreach ($categories as $key => $label): ?>
                    <a href="carte.php?cat=<?= $key ?>&regime=<?= urlencode($filtre_reg) ?>"
                       class="filter-btn <?= $categorie === $key ? 'active' : '' ?>"
                       data-cat="<?= $key ?>">
                        <?= $label ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="filter-group">
            <span>Régime alimentaire</span>
            <div class="filter-buttons">
                <a href="carte.php?cat=<?= $categorie ?>&regime=sans_gluten"
                   class="filter-btn <?= $filtre_reg === 'sans_gluten' ? 'active' : '' ?>"
                   data-regime="sans_gluten">Sans Gluten</a>
                <a href="carte.php?cat=<?= $categorie ?>&regime=sans_lactose"
                   class="filter-btn <?= $filtre_reg === 'sans_lactose' ? 'active' : '' ?>"
                   data-regime="sans_lactose">Sans Lactose</a>
                <a href="carte.php?cat=<?= $categorie ?>&regime=vegetarien"
                   class="filter-btn <?= $filtre_reg === 'vegetarien' ? 'active' : '' ?>"
                   data-regime="vegetarien">Végétarien</a>
                <a href="carte.php?cat=<?= $categorie ?>&regime=vegan"
                   class="filter-btn <?= $filtre_reg === 'vegan' ? 'active' : '' ?>"
                   data-regime="vegan">Vegan</a>
                <a href="carte.php?cat=<?= $categorie ?>&regime=halal"
                   class="filter-btn <?= $filtre_reg === 'halal' ? 'active' : '' ?>"
                   data-regime="halal">Halal</a>
                <?php if ($filtre_reg): ?>
                    <a href="carte.php?cat=<?= $categorie ?>" class="filter-btn">Effacer</a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Tri côté client JS sans rechargement -->
    <div class="tri-bar">
        <label for="select-tri">Trier par :</label>
        <select id="select-tri" class="select-tri">
            <option value="">-- Ordre par défaut --</option>
            <option value="prix-asc">Prix croissant</option>
            <option value="prix-desc">Prix décroissant</option>
            <option value="nom-asc">Nom A → Z</option>
            <option value="nom-desc">Nom Z → A</option>
            <option value="populaire">Les plus commandés</option>
        </select>
    </div>

    <!-- id="grille-plats" ciblé par le JS pour mise à jour asynchrone -->
    <?php if (empty($plats_affiches)): ?>
        <div id="grille-plats"><p class="texte-vide">Aucun plat pour ces critères.</p></div>
    
    <?php else: ?>
    <div id="grille-plats" class="grid-pizzas">
        <?php foreach ($plats_affiches as $plat): ?>
        <article class="pizza-card" data-nb-commandes="<?= $comptage_plats[$plat['id']] ?? 0 ?>">
            <img src="<?= htmlspecialchars($plat['image']) ?>"
                 alt="<?= htmlspecialchars($plat['nom']) ?>"
                 class="pizza-img"
                 onerror="this.src='images/margherita.jpg'">
            <div class="pizza-info">
                <h3><?= htmlspecialchars($plat['nom']) ?></h3>
                <p><?= htmlspecialchars($plat['description']) ?></p>
                <?php if (!empty($plat['allergenes'])): ?>
                    <p class="allergenes"> <?= implode(', ', $plat['allergenes']) ?></p>
                <?php endif; ?>
                <span class="price"><?= number_format($plat['prix'], 2) ?> €</span>
                <?php if (get_role_connecte() === 'client' || !est_connecte()): ?>
                    <a href="panier.php?action=ajouter&type=plat&id=<?= $plat['id'] ?>" class="btn-add">Ajouter au panier</a>
                <?php endif; ?>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if ($categorie === 'toutes'): ?>
    <section class="menu-section">
        <h2>Nos Menus</h2>
        <div class="grid-pizzas">
            <?php foreach ($menus as $menu): ?>
            <article class="pizza-card">
                <img src="<?= htmlspecialchars($menu['image']) ?>"
                     alt="<?= htmlspecialchars($menu['nom']) ?>"
                     class="pizza-img"
                     onerror="this.src='images/margherita.jpg'">
                <div class="pizza-info">
                    <h3><?= htmlspecialchars($menu['nom']) ?></h3>
                    <p><?= htmlspecialchars($menu['description']) ?></p>
                    <?php
                    $noms_plats = array_filter(array_map(function($pid) use ($tous_plats) {
                        foreach ($tous_plats as $p) { if ($p['id'] === $pid) return $p['nom']; }
                        return null;
                    }, $menu['plats_ids'] ?? []));
                    if (!empty($noms_plats)): ?>
                        <p class="allergenes"><?= htmlspecialchars(implode(', ', $noms_plats)) ?></p>
                    <?php endif; ?>
                    <?php if ($menu['creneaux'] === 'midi'): ?>
                        <p class="allergenes"> Disponible midi uniquement</p>
                    <?php endif; ?>
                    <?php if ($menu['personnes_min'] > 1): ?>
                        <p class="allergenes"> Min. <?= $menu['personnes_min'] ?> personnes</p>
                    <?php endif; ?>
                    <span class="price"><?= number_format($menu['prix_total'], 2) ?> €</span>
                    <?php if (get_role_connecte() === 'client' || !est_connecte()): ?>
                        <a href="panier.php?action=ajouter&type=menu&id=<?= $menu['id'] ?>" class="btn-add">Ajouter au panier </a>
                    <?php endif; ?>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
</main>
<footer><p>&copy; 2025-2026 Projet Pizza Nova -préING2- Ibrahim, Ikram &amp; Matthieu</p></footer>
</body>
</html>
