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
}
