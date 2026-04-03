<?php
require_once(__DIR__ . '/includes/session.php');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Qui sommes-nous - Pizza Nova</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body>
<?php $base = '';
require_once(__DIR__ . '/includes/nav.php'); ?>
<main class="container">
    <section class="histoire-centenaire">
        <h1>Pizza Nova : Une Tradition familiale depuis 1970</h1>

        <div class="identite-grid">
            <div class="texte-identite">
                <h3>Trois Générations de Passion</h3>
                <p>L'aventure Pizza Nova a commencé il y a exactement 56 ans, dans les ruelles ensoleillées de Naples,
                avant que mon grand-père Vito n'apporte ses secrets de fabrication ici pour les études de son fils à
                EISTI (aujourd'hui CY TECH). À l'époque, il n'avait qu'un vieux four à bois et une recette de pâte
                unique, transmise de génération en génération.</p>

                <p>Aujourd'hui, même si nous avons grandi, l'esprit reste le même. Chez nous, on ne parle pas de
                clients, mais de "famille". Chaque matin, nous sélectionnons nos tomates San Marzano et notre
                mozzarella fior di latte avec la même exigence que nos ancêtres. Notre levain, que nous entretenons
                jalousement, a plus de 50 ans d'âge !</p>

                <div class="conteneur-image">
                    <img src="../images/pizzeria.png.png" alt="Pizzeria à sa création" class="pizza-img"
                         onerror="this.src='images/margherita.jpg'">
                </div>

                <section class="notre-equipe">
                    <h3>Le Renouveau : L'ère de CY Tech</h3>
                    <p>En 2025, un nouveau chapitre s'écrit pour Pizza Nova. Pour faire entrer cette institution dans
                    l'ère du numérique sans perdre son âme, la famille a confié les rênes à trois jeunes talents de
                    <strong>CY Tech</strong> : <strong>Ibrahim, Ikram et Matthieu</strong>.</p>

                    <p><strong>Matthieu</strong>, véritable maître dans l'art du code et des sites, veille à ce que
                    chaque commande soit une partition sans fausse note.
                    <strong>Ikram</strong>, l'excellence en une personne : la chef en cuisine et au projet, assure
                    la transition et le planning.
                    <strong>Ibrahim</strong>, client fidèle depuis toujours et expert en structure, a solidifié les
                    fondations techniques du site.</p>
                </section>

                <h3>Le Respect du Produit</h3>
                <p>Pourquoi nos pizzas ont-elles ce goût si particulier ? Parce que nous respectons le temps. Notre
                pâte repose pendant 72 heures minimum pour garantir une légèreté et un croustillant incomparables.</p>

                <div class="citation-familiale">
                    <blockquote>"Une pizza n'est pas qu'un repas, c'est un morceau de notre histoire que nous
                    partageons avec vous et une expérience unique."</blockquote>
                    <span>— La Famille Nova</span>
                </div>
            </div>

            <div class="infos-pratiques">
                <h4>📍 Nous trouver</h4>
                <p>4 Rue du Prieuré, 95000 Cergy</p>
                <h4>📞 Contact</h4>
                <p>01 02 03 04 05</p>
                <h4>📅 Horaires</h4>
                <p>Lun–Dim : 11h30–14h30<br>18h30–23h00</p>
                <h4>👤 Équipe</h4>
                <p>Ibrahim, Ikram &amp; Matthieu<br>préING2 - CY Tech</p>
            </div>
        </div>
    </section>
</main>
<footer>
    <p>&copy; 2025-2026 Projet Pizza Nova -préING2- Ibrahim, Ikram &amp; Matthieu</p>
</footer>
</body>
</html>
