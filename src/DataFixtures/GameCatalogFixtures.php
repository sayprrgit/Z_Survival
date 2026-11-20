<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Character;
use App\Entity\Difficulty;
use App\Entity\EnemyType;
use App\Entity\PowerUp;
use App\Entity\Talent;
use App\Entity\Weapon;
use App\Enum\CharacterCode;
use App\Enum\DifficultyCode;
use App\Enum\EnemyCode;
use App\Enum\PowerUpCode;
use App\Enum\TalentBranch;
use App\Enum\WeaponCode;
use BackedEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

final class GameCatalogFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $weapons = [
            WeaponCode::Pistol->value => $this->getOrCreate($manager, Weapon::class, 'weapons', WeaponCode::Pistol, fn (): Weapon => new Weapon(
                WeaponCode::Pistol,
                'Pistolet',
                'Une arme de poing fiable pour les courtes et moyennes distances.',
                'targeted',
                15,
                0.8,
                'Tire un projectile supplémentaire.',
                range: 380,
            )),
            WeaponCode::AssaultRifle->value => $this->getOrCreate($manager, Weapon::class, 'weapons', WeaponCode::AssaultRifle, fn (): Weapon => new Weapon(
                WeaponCode::AssaultRifle,
                'Fusil d’assaut',
                'Une arme automatique polyvalente.',
                'rapid',
                10,
                0.3,
                'Les projectiles perforent les ennemis.',
                range: 440,
            )),
            WeaponCode::Shotgun->value => $this->getOrCreate($manager, Weapon::class, 'weapons', WeaponCode::Shotgun, fn (): Weapon => new Weapon(
                WeaponCode::Shotgun,
                'Fusil à pompe',
                'Tire plusieurs plombs en éventail à courte portée.',
                'spread',
                40,
                1.5,
                'Élargit la dispersion.',
                range: 260,
                projectileCount: 5,
            )),
            WeaponCode::Mace->value => $this->getOrCreate($manager, Weapon::class, 'weapons', WeaponCode::Mace, fn (): Weapon => new Weapon(
                WeaponCode::Mace,
                'Masse',
                'Une arme lourde qui frappe les ennemis autour du survivant.',
                'melee_area',
                35,
                1.8,
                'Déclenche une onde de choc.',
                range: 120,
                areaOfEffect: 120,
            )),
            WeaponCode::Knife->value => $this->getOrCreate($manager, Weapon::class, 'weapons', WeaponCode::Knife, fn (): Weapon => new Weapon(
                WeaponCode::Knife,
                'Couteau',
                'Une arme rapide de très courte portée.',
                'melee',
                12,
                0.5,
                'Les attaques appliquent un saignement.',
                range: 90,
            )),
            WeaponCode::Molotov->value => $this->getOrCreate($manager, Weapon::class, 'weapons', WeaponCode::Molotov, fn (): Weapon => new Weapon(
                WeaponCode::Molotov,
                'Molotov',
                'Enflamme une zone et inflige des dégâts persistants.',
                'area_damage',
                8,
                4,
                'Agrandit la zone de feu.',
                range: 320,
                areaOfEffect: 150,
            )),
        ];

        $characters = [
            [
                CharacterCode::Mason,
                'Mason',
                'Un soldat entraîné, adapté à toutes les situations.',
                '+15 % de dégâts et +10 % de cadence de tir.',
                120,
                'assault_rifle',
                ['availableByDefault' => true, 'damageBonus' => 0.15, 'attackSpeedBonus' => 0.10],
            ],
            [
                CharacterCode::Raven,
                'Raven',
                'Une éclaireuse rapide qui mise sur les coups critiques.',
                '+25 % de vitesse et +15 points de chance critique.',
                80,
                'pistol',
                ['unlockCost' => 250, 'unlockDescription' => 'Coût de déblocage indicatif, à équilibrer.', 'movementSpeedBonus' => 0.25, 'criticalChanceBonus' => 0.15],
            ],
            [
                CharacterCode::Boris,
                'Boris',
                'Un mécanicien robuste qui encaisse les attaques.',
                '-20 % de vitesse et -20 % de dégâts subis.',
                180,
                'mace',
                ['unlockCost' => 350, 'unlockDescription' => 'Coût de déblocage indicatif, à équilibrer.', 'movementSpeedBonus' => -0.20, 'damageReduction' => 0.20],
            ],
            [
                CharacterCode::Eva,
                'Eva',
                'Une médecin capable de récupérer et de renforcer les soins.',
                'Régénération naturelle et +25 % d’efficacité des soins.',
                100,
                'knife',
                ['unlockCost' => 350, 'unlockDescription' => 'Coût de déblocage indicatif, à équilibrer.', 'healthRegeneration' => 0.2, 'healingEffectivenessBonus' => 0.25],
            ],
        ];

        foreach ($characters as [$code, $name, $description, $passive, $health, $startingWeaponCode, $bonuses]) {
            $this->getOrCreate(
                $manager,
                Character::class,
                'characters',
                $code,
                fn (): Character => new Character(
                    $code,
                    $name,
                    $description,
                    $passive,
                    $health,
                    $weapons[$startingWeaponCode],
                    ...$bonuses,
                ),
            );
        }

        $powerUps = [
            [PowerUpCode::ReinforcedAmmo, 'Munitions renforcées', '+10 % de dégâts par niveau.', 'damage_bonus', 0.10],
            [PowerUpCode::Reflexes, 'Réflexes', '+8 % de cadence par niveau.', 'attack_speed_bonus', 0.08],
            [PowerUpCode::ReinforcedVest, 'Gilet renforcé', '+15 PV maximum par niveau.', 'max_health', 15],
            [PowerUpCode::Adrenaline, 'Adrénaline', '+5 % de vitesse par niveau.', 'movement_speed_bonus', 0.05],
            [PowerUpCode::Precision, 'Précision', '+5 points de chance critique par niveau.', 'critical_chance_bonus', 0.05],
            [PowerUpCode::Regeneration, 'Régénération', '+0,3 PV/s par niveau.', 'health_regeneration', 0.3],
            [PowerUpCode::Magnet, 'Aimant', '+15 % de portée de collecte par niveau.', 'pickup_range_bonus', 0.15],
            [PowerUpCode::SurvivalInstinct, 'Instinct de survie', '+10 % d’expérience par niveau.', 'experience_bonus', 0.10],
        ];

        foreach ($powerUps as [$code, $name, $description, $effectType, $bonusPerLevel]) {
            $this->getOrCreate(
                $manager,
                PowerUp::class,
                'power_ups',
                $code,
                fn (): PowerUp => new PowerUp($code, $name, $description, $effectType, $bonusPerLevel),
            );
        }

        $enemyTypes = [
            [EnemyCode::Walker, 'Walker', 'Un zombie lent qui attaque au contact.', 25, 8, 32, 24, 1.4, false, 10, 0, 5, 0.08, 1, 2],
            [EnemyCode::Runner, 'Runner', 'Un zombie rapide et fragile.', 15, 7, 68, 22, 1.0, false, 5, 120, 7, 0.10, 1, 3],
            [EnemyCode::Brute, 'Brute', 'Un zombie massif, lent et résistant.', 150, 24, 20, 28, 1.8, false, 2, 240, 20, 0.25, 3, 8],
            [EnemyCode::Spitter, 'Spitter', 'Un zombie qui attaque à distance.', 50, 12, 22, 180, 2.2, false, 2, 420, 12, 0.12, 2, 5],
            [EnemyCode::Colossus, 'Le Colosse', 'Un boss qui charge et frappe dans une large zone.', 1000, 35, 38, 42, 1.6, true, 0, 300, 100, 0, 0, 0],
            [EnemyCode::PatientZero, 'Patient Zéro', 'Le boss final invoque des zombies et lance des attaques renforcées.', 4000, 40, 30, 220, 1.8, true, 0, EnemyCode::PATIENT_ZERO_AVAILABLE_FROM_SECONDS, 250, 0, 0, 0],
        ];

        foreach ($enemyTypes as [$code, $name, $description, $health, $damage, $speed, $range, $interval, $isBoss, $spawnWeight, $availableFrom, $experience, $scrapChance, $minimumScrap, $maximumScrap]) {
            $this->getOrCreate(
                $manager,
                EnemyType::class,
                'enemy_types',
                $code,
                fn (): EnemyType => new EnemyType(
                    $code,
                    $name,
                    $description,
                    $health,
                    $damage,
                    $speed,
                    $range,
                    $interval,
                    $isBoss,
                    $spawnWeight,
                    $availableFrom,
                    $experience,
                    $scrapChance,
                    $minimumScrap,
                    $maximumScrap,
                ),
            );
        }

        foreach ([
            [DifficultyCode::Normal, 'Normal', 1.0, 1],
            [DifficultyCode::Hard, 'Difficile', 1.3, 2],
            [DifficultyCode::Nightmare, 'Cauchemar', 1.6, 3],
        ] as [$code, $name, $multiplier, $sortOrder]) {
            $this->getOrCreate(
                $manager,
                Difficulty::class,
                'difficulties',
                $code,
                fn (): Difficulty => new Difficulty($code, $name, 'Mode de difficulté du jeu.', $multiplier, $sortOrder),
            );
        }

        $talents = [];
        $talentDefinitions = [
            [TalentBranch::Survival, [
                ['endurance', 'Endurance', '+10 PV de base.', 100, 'max_health', 10],
                ['armor', 'Armure', '-3 % de dégâts subis.', 175, 'damage_reduction', 0.03],
                ['healing', 'Guérison', '+0,2 PV/s.', 250, 'health_regeneration', 0.2],
                ['resistance', 'Résistance', 'Réduction supplémentaire des dégâts (valeur indicative).', 325, 'damage_reduction', 0.05],
                ['survivor', 'Survivant', '+25 PV de base.', 400, 'max_health', 25],
            ]],
            [TalentBranch::Combat, [
                ['power', 'Puissance', '+5 % de dégâts.', 100, 'damage_bonus', 0.05],
                ['cadence', 'Cadence', '+5 % de cadence.', 175, 'attack_speed_bonus', 0.05],
                ['critical', 'Critique', '+3 points de chance critique.', 250, 'critical_chance_bonus', 0.03],
                ['perforation', 'Perforation', '+10 % de dégâts.', 325, 'damage_bonus', 0.10],
                ['executor', 'Exécuteur', '+15 % de dégâts.', 400, 'damage_bonus', 0.15],
            ]],
            [TalentBranch::Utility, [
                ['agility', 'Agilité', '+5 % de vitesse.', 100, 'movement_speed_bonus', 0.05],
                ['collection', 'Collecte', '+10 % de portée de collecte.', 175, 'pickup_range_bonus', 0.10],
                ['luck', 'Chance', '+5 % de probabilité de butin.', 250, 'loot_chance_bonus', 0.05],
                ['experience', 'Expérience', '+10 % d’expérience.', 325, 'experience_bonus', 0.10],
                ['raider', 'Pillard', '+20 % de ferraille.', 400, 'scrap_bonus', 0.20],
            ]],
        ];

        foreach ($talentDefinitions as [$branch, $definitions]) {
            $branchTalentCodes = [];
            foreach ($definitions as $index => [$code, $name, $description, $cost, $effectType, $effectValue]) {
                $talents[$code] = $this->getOrCreate(
                    $manager,
                    Talent::class,
                    'talents',
                    $code,
                    fn (): Talent => new Talent($code, $name, $description, $branch, $index + 1, $cost, $effectType, $effectValue),
                );
                $branchTalentCodes[] = $code;
            }

            foreach ($branchTalentCodes as $index => $code) {
                if ($index > 0) {
                    $talents[$code]->setParent($talents[$branchTalentCodes[$index - 1]]);
                }
            }
        }

        $manager->flush();
    }

    /** @param class-string $entityClass */
    private function getOrCreate(
        ObjectManager $manager,
        string $entityClass,
        string $tableName,
        BackedEnum|string $code,
        callable $factory,
    ): object {
        $codeValue = $code instanceof BackedEnum ? (string) $code->value : $code;
        $existing = $this->findExisting($manager, $entityClass, $tableName, $codeValue);

        if ($existing !== null) {
            return $existing;
        }

        $entity = $factory();
        $manager->persist($entity);

        return $entity;
    }

    /** @param class-string $entityClass */
    private function findExisting(ObjectManager $manager, string $entityClass, string $tableName, string $code): ?object
    {
        if ($manager instanceof EntityManagerInterface) {
            $id = $manager->getConnection()->fetchOne(sprintf('SELECT id FROM %s WHERE code = ?', $tableName), [$code]);

            return $id === false ? null : $manager->find($entityClass, (int) $id);
        }

        return $manager->getRepository($entityClass)->findOneBy(['code' => $code]);
    }
}
