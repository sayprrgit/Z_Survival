# Z_Survival

Z_Survival est un jeu d'action roguelite **survivors-like** jouable dans un navigateur PC. Dans un univers post-apocalyptique infesté de zombies, le joueur se déplace dans une arène en vue isométrique, combat automatiquement des hordes et choisit des améliorations pour survivre jusqu'aux boss.

Le projet privilégie d'abord une boucle de jeu complète et jouable. La progression permanente permet de conserver de la ferraille, d'acheter des talents et de débloquer des personnages entre les parties.

## Fonctionnalités prévues

- Création de compte et authentification.
- Sélection et déblocage de personnages.
- Parties avec déplacements, combat automatique, ennemis, butin et améliorations temporaires.
- Difficulté progressive et combats de boss.
- Récompenses et historique des parties.
- Arbre de talents et progression persistante.
- Profil avec statistiques de jeu.

Le périmètre MVP réduit le contenu initial (personnages, armes, bonus, ennemis, boss et talents). Le contenu supplémentaire vient après validation de la boucle principale.

## Technologies

- PHP 8.4+ et Symfony 8.1
- Twig, HTML et CSS pour les interfaces
- JavaScript ES6+ et Canvas 2D pour le moteur temps réel isométrique
- Doctrine ORM et Doctrine Migrations
- MySQL comme base cible
- PHPUnit

La simulation de combat s'exécute dans le navigateur. Symfony fournit la configuration de départ, gère les comptes et la progression persistante, puis valide et enregistre les résultats des parties. Aucune requête SQL n'est prévue pendant la boucle de jeu.

## Prérequis

- PHP 8.4 ou plus récent avec les extensions requises par Symfony et Doctrine
- Composer
- Un serveur de base de données configuré pour le projet
- Symfony CLI (recommandé)

## Installation et lancement

1. Récupérer le projet et installer ses dépendances :

   ```bash
   composer install
   ```

2. Configurer `DATABASE_URL` dans `.env.local` pour votre base locale. Le cahier des charges cible MySQL. Ne placez pas de mot de passe réel dans `.env`.

3. Créer la base et appliquer les migrations, lorsqu'elles sont disponibles :

   ```bash
   symfony console doctrine:database:create
   symfony console doctrine:migrations:migrate
   ```

4. Charger les données de démonstration si les fixtures sont prêtes et installées :

   ```bash
   symfony console doctrine:fixtures:load
   ```

5. Démarrer le serveur :

   ```bash
   symfony serve
   ```

### Configuration de base de données actuelle

La configuration présente dans le dépôt (`.env` et `compose.yaml`) utilise encore PostgreSQL. Elle doit être alignée sur MySQL avant d'utiliser la configuration Docker fournie pour l'environnement cible du projet. `DATABASE_URL` peut être défini localement dans `.env.local`.

## Organisation du code

- `src/Entity`, `src/Repository` : persistance Doctrine
- `src/Controller` : routes et orchestration HTTP
- `src/Game` : logique métier PHP (personnages, armes, talents, parties et récompenses)
- `src/DTO`, `src/Enum` : données de transfert et valeurs métier
- `src/DataFixtures` : données de démonstration
- `assets/game` : moteur JavaScript, entités, systèmes et rendu
- `templates` : interfaces Twig
- `migrations` : évolutions du schéma de base
- `tests` : tests automatisés

Les entités principales couvrent les utilisateurs, personnages, armes, power-ups, types d'ennemis, difficultés, talents, acquisitions de progression et parties. Les ennemis et objets temporaires ne sont pas persistés individuellement.

## Principes de conception

- Séparer les responsabilités entre contrôleurs, services métier et moteur JavaScript.
- Utiliser l'injection de dépendances et la programmation orientée objet.
- Vérifier côté serveur la propriété des parties, les achats, les déblocages et l'attribution des récompenses.
- Garder le périmètre concentré sur un MVP réalisable dans le temps imparti.

## Contrôles prévus

- `ZQSD` ou `WASD` : déplacement
- `Échap` : pause
- Souris : navigation dans les menus et choix d'amélioration

## État du projet

Le dépôt contient le socle Symfony, les premières entités et repositories, les services métier du jeu et une première structure du moteur JavaScript. Les migrations, fixtures, interfaces et fonctionnalités sont à compléter selon l'avancement du développement.
