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
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class GameCatalogFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $pistol = new Weapon(
            WeaponCode::Pistol,
            'Pistolet',
            'Une arme de poing fiable pour les courtes et moyennes distances.',
            'targeted',
            15,
            0.8,
            'Tire un projectile supplémentaire.',
            range: 380,
        );
        $assaultRifle = new Weapon(
            WeaponCode::AssaultRifle,
            'Fusil d’assaut',
            'Une arme automatique polyvalente.',
            'rapid',
            10,
            0.3,
            'Les projectiles perforent les ennemis.',
            range: 440,
        );
        $shotgun = new Weapon(
            WeaponCode::Shotgun,
            'Fusil à pompe',
            'Tire plusieurs plombs en éventail à courte portée.',
            'spread',
            40,
            1.5,
            'Élargit la dispersion.',
            range: 260,
            projectileCount: 5,
        );

        foreach ([$pistol, $assaultRifle, $shotgun] as $weapon) {
            $manager->persist($weapon);
        }

        $manager->persist(new Character(
            CharacterCode::Mason,
            'Mason',
            'Un soldat entraîné, adapté à toutes les situations.',
            '+15 % de dégâts et +10 % de cadence de tir.',
            120,
            $assaultRifle,
            availableByDefault: true,
            damageBonus: 0.15,
            attackSpeedBonus: 0.10,
        ));
        $manager->persist(new Character(
            CharacterCode::Raven,
            'Raven',
            'Une éclaireuse rapide qui mise sur les coups critiques.',
            '+25 % de vitesse et +15 points de chance critique.',
            80,
            $pistol,
            unlockCost: 250,
            unlockDescription: 'Personnage déblocable avec de la ferraille (coût indicatif MVP).',
            movementSpeedBonus: 0.25,
            criticalChanceBonus: 0.15,
        ));

        foreach ([
            new PowerUp(PowerUpCode::ReinforcedAmmo, 'Munitions renforcées', '+10 % de dégâts par niveau.', 'damage_bonus', 0.10),
            new PowerUp(PowerUpCode::Reflexes, 'Réflexes', '+8 % de cadence par niveau.', 'attack_speed_bonus', 0.08),
            new PowerUp(PowerUpCode::ReinforcedVest, 'Gilet renforcé', '+15 PV maximum par niveau.', 'max_health', 15),
            new PowerUp(PowerUpCode::Adrenaline, 'Adrénaline', '+5 % de vitesse par niveau.', 'movement_speed_bonus', 0.05),
        ] as $powerUp) {
            $manager->persist($powerUp);
        }

        foreach ([
            new EnemyType(EnemyCode::Walker, 'Walker', 'Un zombie lent qui attaque au contact.', 25, 8, 32, 24, 1.4, spawnWeight: 10, experienceReward: 5, scrapDropChance: 0.08, minimumScrapDrop: 1, maximumScrapDrop: 2),
            new EnemyType(EnemyCode::Runner, 'Runner', 'Un zombie rapide et fragile.', 15, 7, 68, 22, 1.0, spawnWeight: 5, availableFromSeconds: 120, experienceReward: 7, scrapDropChance: 0.10, minimumScrapDrop: 1, maximumScrapDrop: 3),
            new EnemyType(EnemyCode::Brute, 'Brute', 'Un zombie massif, lent et résistant.', 150, 24, 20, 28, 1.8, spawnWeight: 2, availableFromSeconds: 240, experienceReward: 20, scrapDropChance: 0.25, minimumScrapDrop: 3, maximumScrapDrop: 8),
            new EnemyType(EnemyCode::Colossus, 'Le Colosse', 'Un boss qui charge et frappe dans une large zone.', 1000, 35, 38, 42, 1.6, isBoss: true, spawnWeight: 0, availableFromSeconds: 300, experienceReward: 100),
        ] as $enemyType) {
            $manager->persist($enemyType);
        }

        $manager->persist(new Difficulty(
            DifficultyCode::Normal,
            'Normal',
            'Le mode de difficulté de départ.',
            1.0,
            1,
        ));

        $talents = [
            'survival' => [
                new Talent('endurance', 'Endurance', '+10 PV de base.', TalentBranch::Survival, 1, 100, 'max_health', 10),
                new Talent('armor', 'Armure', '-3 % de dégâts subis.', TalentBranch::Survival, 2, 175, 'damage_reduction', 0.03),
                new Talent('healing', 'Guérison', '+0,2 PV/s.', TalentBranch::Survival, 3, 250, 'health_regeneration', 0.2),
            ],
            'combat' => [
                new Talent('power', 'Puissance', '+5 % de dégâts.', TalentBranch::Combat, 1, 100, 'damage_bonus', 0.05),
                new Talent('cadence', 'Cadence', '+5 % de cadence.', TalentBranch::Combat, 2, 175, 'attack_speed_bonus', 0.05),
                new Talent('critical', 'Critique', '+3 points de chance critique.', TalentBranch::Combat, 3, 250, 'critical_chance_bonus', 0.03),
            ],
            'utility' => [
                new Talent('agility', 'Agilité', '+5 % de vitesse.', TalentBranch::Utility, 1, 100, 'movement_speed_bonus', 0.05),
                new Talent('collection', 'Collecte', '+10 % de portée de collecte.', TalentBranch::Utility, 2, 175, 'pickup_range_bonus', 0.10),
                new Talent('luck', 'Chance', '+5 % de probabilité de butin.', TalentBranch::Utility, 3, 250, 'loot_chance_bonus', 0.05),
            ],
        ];

        foreach ($talents as $branchTalents) {
            $branchTalents[1]->setParent($branchTalents[0]);
            $branchTalents[2]->setParent($branchTalents[1]);

            foreach ($branchTalents as $talent) {
                $manager->persist($talent);
            }
        }

        $manager->flush();
    }
}
