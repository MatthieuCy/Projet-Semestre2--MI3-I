<?php
require_once(__DIR__ . '/includes/session.php');
require_once(__DIR__ . '/includes/donnees.php');

// Calculer les 3 plats les plus commandés dynamiquement
$comptage = [];
foreach (get_toutes_commandes() as $cmd) {
    foreach ($cmd['articles'] ?? [] as $art) {
        if ($art['type'] === 'plat') {
            $comptage[$art['id']] = ($comptage[$art['id']] ?? 0) + ($art['quantite'] ?? 1);
        }
    }
}
arsort($comptage);
$top_ids = array_slice(array_keys($comptage), 0, 3);
$tous_plats = get_tous_plats();
$plats_phares = array_filter($tous_plats, fn($p) => in_array($p['id'], $top_ids));
$menus = get_tous_menus();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pizza Nova - Accueil</title>
</head>
<body>
<?php $base = '';
require_once(__DIR__ . '/includes/nav.php'); ?>

<section class="hero">
    <div class="hero-container">
        <div class="hero-image">
            <img src="images/Vito.png" alt="Vito Nova">
        </div>
        <div class="hero-content">
            <h1>L'excellence de la pizzeria façon CY</h1>
            <h2>Une expérience artisanale unique.</h2>
            <div class="search-box">
                <label for="rech">Rechercher :</label>
                <form action="carte.php" method="get" style="display:flex;gap:8px;">
                    <input type="text" id="rech" name="recherche" class="search-input"
                           placeholder="Ex: Margherita...">
                    <button type="submit" class="btn-ok">Ok</button>
                </form>
            </div>
        </div>
    </div>
</section>

<section class="featured container-mobile">
    <h2 class="section-title">Pourquoi choisir Pizza Nova ?</h2>
    <div class="grid-pizzas">
        <article class="commande">
            <h3 class="highlight-title">Savoir-faire Artisanal</h3>
            <img src="images/savoir-faire.jpg" alt="Pétrissage de la pâte" class="pizza-img">
            <p>Nos pizzaïolos travaillent une pâte pétrie chaque matin, laissée au repos 72h pour une légèreté incomparable.</p>
        </article>
        <article class="commande">
            <h3 class="highlight-title">Ingrédients de qualité</h3>
            <img src="images/ingredients.jpg" alt="Ingrédients frais" class="pizza-img">
            <p>De la mozzarella fior di latte aux tomates bio de saison, nous sélectionnons uniquement le meilleur pour vos papilles.</p>
        </article>
        <article class="commande">
            <h3 class="highlight-title">Livraison instantanée</h3>
            <img src="images/livraison.jpg" alt="Livreur de pizza" class="pizza-img">
            <p>Commandez en quelques clics et recevez votre pizza fumante grâce à notre réseau de livreurs ultra-réactifs.</p>
        </article>
    </div>
</section>

<!-- Plats les plus commandés -->
<section class="featured container-mobile">
    <h2>Les plus commandés</h2>
    <div class="grid-pizzas">
        <?php foreach ($plats_phares as $plat): ?>
        <article class="pizza-card">
            <img src="<?= htmlspecialchars($plat['image']) ?>"
                 alt="<?= htmlspecialchars($plat['nom']) ?>"
                 class="pizza-img"
                 onerror="this.src='images/margherita.jpg'">
            <div class="pizza-info">
                <h3><?= htmlspecialchars($plat['nom']) ?></h3>
                <p><?= htmlspecialchars($plat['description']) ?></p>
                <span class="price"><?= number_format($plat['prix'], 2) ?> €</span>
                <a href="carte.php" class="btn-add">Voir la carte </a>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="featured container-mobile">
    <h2>Vous ne savez pas quoi choisir ?</h2>
    <p style="text-align:center;margin-bottom:16px;">Laissez-nous décider pour vous !</p>
    <div style="text-align:center;">
        <button id="btn-surprise" class="btn-main">Menu surprise</button>
    </div>
</section>

<!-- Modale menu aléatoire -->
<div id="modale-surprise" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:1000;justify-content:center;align-items:center;">
    <div style="background:var(--creme);border-radius:12px;padding:32px;max-width:400px;width:90%;text-align:center;">
        <h3 id="surprise-nom"></h3>
        <p id="surprise-desc" style="margin:12px 0;"></p>
        <p id="surprise-prix" style="font-weight:bold;font-size:1.2em;margin-bottom:20px;"></p>
        <div style="display:flex;gap:12px;justify-content:center;">
            <a id="surprise-lien" href="#" class="btn-main">Ajouter au panier</a>
            <button onclick="fermerSurprise()" class="btn-ok">Fermer</button>
        </div>
    </div>
</div>

<script>
const menus = <?= json_encode(array_values($menus), JSON_UNESCAPED_UNICODE) ?>;

document.getElementById('btn-surprise').addEventListener('click', function() {
    const menu = menus[Math.floor(Math.random() * menus.length)];
    document.getElementById('surprise-nom').textContent  = menu.nom;
    document.getElementById('surprise-desc').textContent = menu.description;
    document.getElementById('surprise-prix').textContent = parseFloat(menu.prix_total).toFixed(2) + ' €';
    document.getElementById('surprise-lien').href        = 'panier.php?action=ajouter&type=menu&id=' + menu.id;
    const modale = document.getElementById('modale-surprise');
    modale.style.display = 'flex';
});

function fermerSurprise() {
    document.getElementById('modale-surprise').style.display = 'none';
}
</script>
    <div class="carte-livraison">
        <h3> Nos Horaires</h3>
        <p>Lundi – Dimanche : 11h30–14h30 / 18h30–23h00</p>
        <br>
        <p> <strong>Localisation :</strong> 4 Rue du Prieuré, 95000 Cergy</p>
        <p> <strong>Téléphone :</strong> 01 02 03 04 05</p>
    </div>
</section>

<footer>
    <p>&copy; 2025-2026 Projet Pizza Nova - préING2 - Ibrahim, Ikram &amp; Matthieu</p>
</footer>
</body>
</html>
