# Architecture — Z_Survival

Le serveur fournit les comptes, la progression permanente, les parties et la
configuration du jeu. Le schéma MySQL est géré par migrations Doctrine ; le
moteur de combat tourne dans le navigateur et consomme les données du serveur.

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

## Parcours serveur disponibles

1. Le schéma MySQL est créé par migrations ; la colonne réservée `range` a été
   renommée en `attack_range`.
2. Les fixtures chargent quatre personnages, neuf armes, huit power-ups, quatre
   zombies, deux boss, quinze talents et trois difficultés.
3. L'inscription hache les mots de passe ; la connexion et la déconnexion
   utilisent Symfony Security et des jetons CSRF. Les pages de jeu et les routes
   de progression exigent `ROLE_USER`.
4. `POST /api/runs/start` crée une partie liée au compte, au personnage débloqué
   et à la difficulté. `POST /api/runs/{id}/finish` vérifie le propriétaire, le
   statut, la durée, le niveau et le nombre d'éliminations, puis sauvegarde la
   partie et verse sa récompense dans une transaction idempotente.
5. `GET /api/profile` et `GET /api/runs?page=1&limit=20` exposent le profil et
   l'historique paginé du compte connecté.
6. `GET /api/characters` et `POST /api/characters/{code}/unlock` exposent et
   gèrent les personnages. Les achats vérifient le solde, débitent la ferraille
   dans une transaction et sont idempotents.
7. `GET /api/talents` et `POST /api/talents/{code}/purchase` exposent l'arbre
   des talents et appliquent les prérequis de branche. Les effets achetés sont
   ajoutés aux statistiques du joueur et aux récompenses de partie.
8. La configuration transmise au moteur comprend les statistiques du personnage
   et talents, armes, power-ups, ennemis, boss et difficultés.

## Limites et suite

- Les statistiques de combat viennent du moteur dans le navigateur. Le serveur
  vérifie les bornes reçues, mais ne rejoue pas les combats. Pour déclarer une
  victoire, le client doit envoyer `bossCode: "patient_zero"` et une durée d'au
  moins 900 secondes. Cette validation écarte les résultats impossibles, mais
  ne prouve pas à elle seule que le boss a réellement été vaincu.
- Le front doit encore relier ses écrans aux routes de catalogues, profil,
  historique, déblocage et talents, avec les jetons CSRF transmis par Symfony.
- Une simulation de combat serveur, la validation indépendante de la victoire
  contre Patient Zéro, la limitation des tentatives de connexion et la
  récupération de mot de passe sont des évolutions hors du MVP actuel.
