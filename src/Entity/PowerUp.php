<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\PowerUpCode;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Repository\PowerUpRepository::class)]
#[ORM\Table(name: 'power_ups')]
final class PowerUp extends AbstractLevelableCatalogItem
{
    #[ORM\Column(enumType: PowerUpCode::class, unique: true)]
    private PowerUpCode $code;

    #[ORM\Column(length: 50)]
    private string $effectType;

    #[ORM\Column]
    private float $bonusPerLevel;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $iconPath = null;

    public function __construct(PowerUpCode $code, string $name, string $description, string $effectType, float $bonusPerLevel)
    {
        parent::__construct($name, $description);
        $this->code = $code;
        $this->effectType = $effectType;
        $this->bonusPerLevel = $bonusPerLevel;
    }

    public function getCode(): PowerUpCode { return $this->code; }
    public function getName(): string { return $this->name; }
    public function getDescription(): string { return $this->description; }
    public function getEffectType(): string { return $this->effectType; }
    public function getBonusPerLevel(): float { return $this->bonusPerLevel; }
    public function getMaxLevel(): int { return $this->maxLevel; }
}
