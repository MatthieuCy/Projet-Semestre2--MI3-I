<?php
require_once '../includes/session.php';
require_once '../includes/donnees.php';

// Plats mis en avant sur la page d'accueil
$plats_phares_ids = [1, 4, 5]; // Margherita, Calzone, Veggie
$plats_phares = array_filter(get_tous_plats(), fn($p) => in_array($p['id'], $plats_phares_ids));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pizza Nova - Accueil</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
<?php $base = '';
require_once '../includes/nav.php'; ?>

<section class="hero">
    <div class="hero-container">
        <div class="hero-image">
            <img src="../images/Vito.png" alt="Vito Nova">
        </div>
        <div class="hero-content">
            <h1>L'excellence de la pizzeria façon CY</h1>
            <h2>Une expérience artisanale unique.</h2>
            <div class="search-box">
                <label for="rech">Rechercher :</label>
                <input type="text" id="rech" name="fname" class="search-input"
                       placeholder="Ex: Margherita...">
                <a href="carte.php" class="btn-ok">Ok</a>
            </div>
        </div>
    </div>
</section>

<section class="featured container-mobile">
    <h2 class="section-title">Pourquoi choisir Pizza Nova ?</h2>
    <div class="grid-pizzas">
        <article class="commande">
            <h3 class="highlight-title">Savoir-faire Artisanal</h3>
            <img src="../images/savoir-faire.jpg" alt="Pétrissage de la pâte" class="pizza-img"
                 onerror="this.src='images/margherita.jpg'">
            <p>Nos pizzaïolos travaillent une pâte pétrie chaque matin, laissée au repos 72h pour une légèreté incomparable.</p>
        </article>
        <article class="commande">
            <h3 class="highlight-title">Ingrédients de qualité</h3>
            <img src="../images/ingredients.jpg" alt="Ingrédients frais" class="pizza-img"
                 onerror="this.src='images/margherita.jpg'">
            <p>De la mozzarella fior di latte aux tomates bio de saison, nous sélectionnons uniquement le meilleur pour vos papilles.</p>
        </article>
        <article class="commande">
            <h3 class="highlight-title">Livraison instantanée</h3>
            <img src="../images/livraison.jpg" alt="Livreur de pizza" class="pizza-img"
                 onerror="this.src='images/margherita.jpg'">
            <p>Commandez en quelques clics et recevez votre pizza fumante grâce à notre réseau de livreurs ultra-réactifs.</p>
        </article>
    </div>
</section>

<!-- Plats du moment -->
<section class="featured container-mobile">
    <h2>🍕 Nos incontournables</h2>
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
                <a href="carte.php" class="btn-add">Voir la carte 🍕</a>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="info-section container-mobile">
    <div class="carte-livraison">
        <h3>📅 Nos Horaires</h3>
        <p>Lundi – Dimanche : 11h30–14h30 / 18h30–23h00</p>
        <br>
        <p>📍 <strong>Localisation :</strong> 4 Rue du Prieuré, 95000 Cergy</p>
        <p>📞 <strong>Téléphone :</strong> 01 02 03 04 05</p>
    </div>
</section>

<footer>
    <p>&copy; 2025-2026 Projet Pizza Nova - préING2 - Ibrahim, Ikram &amp; Matthieu</p>
</footer>
</body>
</html>
