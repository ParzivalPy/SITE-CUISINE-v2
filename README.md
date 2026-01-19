# SITE-CUISINE-v2

site-recettes/
│
├── index.php
├── recette.php
├── categorie.php
├── recherche.php
├── contact.php
│
├── includes/
│   ├── header.php
│   ├── footer.php
│   ├── navbar.php
│   ├── db.php              # connexion à la base de données
│   └── functions.php       # functions PHP utiles (formatage, recherche, etc.)
│
├── assets/
│   ├── images/
│   │   ├── recettes/
│   │   │   ├── gateau-chocolat.jpg
│   │   │   ├── lasagnes.jpg
│   │   │   └── salade-cesar.jpg
│   │   ├── icones/
│   │   │   ├── logo.png
│   │   │   ├── search.svg
│   │   │   └── user.svg
│   │   └── favicon.ico
│   │
│   ├── css/
│   │   ├── style.css
│   │   └── responsive.css
│   │
│   ├── js/
│   │   ├── main.js
│   │   ├── recherche.js
│   │   └── favoris.js
│   │
│   └── fonts/
│       ├── OpenSans-Regular.ttf
│       └── OpenSans-Bold.ttf
│
├── data/
│   └── recettes.json       # exemple si pas de base de données
│
└── admin/
    ├── index.php           # tableau de bord
    ├── ajouter_recette.php
    ├── modifier_recette.php
    ├── supprimer_recette.php
    └── includes/
        └── auth.php        # gestion de l’authentification admin
