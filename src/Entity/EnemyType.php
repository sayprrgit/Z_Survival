<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\EnemyCode;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Repository\EnemyTypeRepository::class)]
#[ORM\Table(name: 'enemy_types')]
final class EnemyType extends AbstractCombatantCatalogItem
{
    #[ORM\Column(enumType: EnemyCode::class, unique: true)]
    private EnemyCode $code;

    #[ORM\Column(options: ['default' => false])]
    private bool $isBoss = false;

    #[ORM\Column]
    private float $baseDamage;

    #[ORM\Column]
    private float $movementSpeed;

    #[ORM\Column]
    private float $attackRange;

    #[ORM\Column]
    private float $attackInterval;

    #[ORM\Column(options: ['default' => 1])]
    private int $spawnWeight = 1;

    #[ORM\Column(options: ['default' => 0])]
    private int $availableFromSeconds = 0;

    #[ORM\Column(options: ['default' => 0])]
    private int $experienceReward = 0;

    #[ORM\Column(options: ['default' => 0])]
    private float $scrapDropChance = 0;

    #[ORM\Column(options: ['default' => 0])]
    private int $minimumScrapDrop = 0;

    #[ORM\Column(options: ['default' => 0])]
    private int $maximumScrapDrop = 0;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $spritePath = null;

    public function __construct(EnemyCode $code, string $name, string $description, int $baseHealth, float $baseDamage, float $movementSpeed, float $attackRange, float $attackInterval)
    {
        parent::__construct($name, $description, $baseHealth);
        $this->code = $code;
        $this->baseDamage = $baseDamage;
        $this->movementSpeed = $movementSpeed;
        $this->attackRange = $attackRange;
        $this->attackInterval = $attackInterval;
    }
}
