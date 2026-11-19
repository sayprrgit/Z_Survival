# Architecture initiale — Z_Survival

Cette première itération installe le vocabulaire, les frontières du projet et le
modèle Doctrine. Aucune migration, table ni route active n'est créée à ce stade.

| Zone | Responsabilité |
| --- | --- |
| `src/Entity` | Modèle Doctrine persistant : compte, catalogue, progression et historique des parties. |
| `src/Game` | Services de domaine et définitions PHP du catalogue de jeu. |
| `src/DTO` | Contrats d'entrée/sortie entre HTTP et le domaine. |
| `src/Controller` | Futurs points d'entrée web et API, volontairement vides pour l'instant. |
| `assets/game` | Moteur Canvas local, séparé du backend Symfony. |

Les classes de configuration (`WeaponDefinition`, `PowerUpDefinition`,
`EnemyDefinition` et `PlayableCharacter`) ne sont pas des entités Doctrine :
elles représentent le comportement/rôle d'un élément de jeu. Les entités
correspondantes sont le catalogue persistant que Symfony transmettra au moteur.

## Modèle de données prêt à migrer

Le collègue chargé de la base de données peut générer une migration à partir des
entités et des relations suivantes :

| Table future | Rôle | Contraintes principales |
| --- | --- | --- |
| `users` | Compte, authentification, ferraille et statistiques de profil | email unique |
| `characters`, `weapons`, `power_ups`, `enemy_types`, `difficulties` | Catalogue de jeu | code unique |
| `talents` | Arbre de talents | code unique, parent optionnel |
| `user_talents`, `user_characters` | Progression permanente | unicité utilisateur/contenu |
| `runs` | Historique des parties | relations vers utilisateur, personnage et difficulté |

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

## Étapes suivantes

1. Générer puis relire la migration Doctrine avec le responsable BDD.
2. Ajouter les fixtures du catalogue et les implémentations des factories.
3. Créer l'authentification et les contrôleurs Twig/API avec leurs tests.
4. Implémenter progressivement le moteur JavaScript et ses systèmes.
