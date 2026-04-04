#  Pizza Nova - Phase 2

## Structure du projet

```
pizza_nova/
├── style.css              ← Feuille de style commune
│
├── vues/                  ← TOUS les fichiers PHP sont ici
│   ├── index.php          → Accueil
│   ├── carte.php          → La carte des produits
│   ├── connexion.php      → Connexion
│   ├── inscription.php    → Inscription
│   ├── identite.php       → Qui sommes-nous
│   ├── deconnexion.php    → Déconnexion
│   ├── profil.php         → Profil client
│   ├── panier.php         → Panier + commande
│   ├── notation.php       → Notation commande
│   ├── admin.php          → Gestion utilisateurs (admin)
│   ├── admin_profil.php   → Détail utilisateur (admin)
│   ├── restaurateur.php   → Tableau de bord cuisine
│   └── livraison.php      → Interface livreur
│
├── includes/              ← Bibliothèques PHP réutilisables
│   ├── donnees.php        → Lecture / écriture JSON
│   ├── session.php        → Authentification et sessions
│   └── nav.php            → Barre de navigation commune
│
├── data/                  ← Fichiers de données JSON
│   ├── utilisateurs.json
│   ├── plats.json
│   ├── menus.json
│   └── commandes.json
│
├── images/               

```

## Comptes de test
| Rôle         | Email              | Mot de passe |
|--------------|--------------------|--------------|
| Client       | client1@pizza.fr   | password     |
| Admin        | admin@pizza.fr     | password     |
| Restaurateur | cuisinier@pizza.fr | password     |
| Livreur      | livreur@pizza.fr   | password     |

## Lancer le projet

### Avec le terminal VS Code
```bash
php -S localhost:8000
```
Puis aller sur : http://localhost:8000


