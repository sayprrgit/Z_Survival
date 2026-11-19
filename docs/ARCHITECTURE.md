# Architecture — Z_Survival

Le socle définit les frontières du projet et le modèle Doctrine. Le schéma MySQL
est créé par migrations ; les routes métier et les services de jeu restent à
compléter.

| Zone | Responsabilité |
| --- | --- |
| `src/Entity` | Modèle Doctrine persistant : compte, catalogue, progression et historique des parties. |
| `src/Game` | Services de domaine et définitions PHP du catalogue de jeu. |
| `src/DTO` | Contrats d'entrée/sortie entre HTTP et le domaine. |
| `src/Controller` | Points d'entrée HTTP minces pour les interfaces et l'API. |
| `assets/game` | Moteur Canvas local, séparé du backend Symfony. |

Les classes de configuration (`WeaponDefinition`, `PowerUpDefinition`,
`EnemyDefinition` et `PlayableCharacter`) ne sont pas des entités Doctrine :
elles représentent le comportement/rôle d'un élément de jeu. Les entités
correspondantes sont le catalogue persistant que Symfony transmettra au moteur.

## Modèle de données

Les entités et relations persistées sont :

| Table | Rôle | Contraintes principales |
| --- | --- | --- |
| `users` | Compte, authentification, ferraille et statistiques de profil | email unique |
| `characters`, `weapons`, `power_ups`, `enemy_types`, `difficulties` | Catalogue de jeu | code unique |
| `talents` | Arbre de talents | code unique, parent optionnel |
| `user_talents`, `user_characters` | Progression permanente | unicité utilisateur/contenu |
| `runs` | Historique des parties | relations vers utilisateur, personnage et difficulté |

Doctrine utilise MySQL 8.4 avec InnoDB, UTF-8 (`utf8mb4`) et des migrations
versionnées. La connexion WAMP locale est configurée dans `.env.local` ; le
fichier `.env` contient les valeurs de développement génériques.

Les types de catalogue et de cycle de vie sont des enums PHP : `CharacterCode`,
`WeaponCode`, `PowerUpCode`, `EnemyCode`, `DifficultyCode`, `TalentBranch`,
`RunStatus` et `RunResult`.

Le modèle réutilise les mapped superclasses Doctrine suivantes afin d'éviter la
duplication sans mélanger des concepts métier différents :

- `AbstractEntity` : identifiant technique commun ;
- `AbstractCatalogItem` : nom et description des éléments de catalogue ;
- `AbstractLevelableCatalogItem` : niveau maximal des armes et power-ups ;
- `AbstractCombatantCatalogItem` : PV de base des personnages et ennemis ;
- `AbstractUserAcquisition` : utilisateur propriétaire et date d'acquisition
  pour les contenus déverrouillés.

## État et étapes suivantes

1. Le schéma initial est migré ; une migration renomme la colonne réservée
   `range` en `attack_range` pour MySQL.
2. Les fixtures chargent le catalogue complet décrit : quatre personnages, six
   armes, huit power-ups, quatre zombies, deux boss, quinze talents et trois
   difficultés.
3. Créer l'authentification et les contrôleurs Twig/API avec leurs tests.
4. Implémenter progressivement les factories et le moteur JavaScript.
