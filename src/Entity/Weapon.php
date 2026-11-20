<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\WeaponCode;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Repository\WeaponRepository::class)]
#[ORM\Table(name: 'weapons')]
final class Weapon extends AbstractLevelableCatalogItem
{
    #[ORM\Column(enumType: WeaponCode::class, unique: true)]
    private WeaponCode $code;

    #[ORM\Column(length: 30)]
    private string $attackType;

    #[ORM\Column]
    private float $baseDamage;

    #[ORM\Column]
    private float $attackInterval;

    #[ORM\Column(name: 'attack_range', options: ['default' => 0])]
    private float $range = 0;

    #[ORM\Column(options: ['default' => 1])]
    private int $projectileCount = 1;

    #[ORM\Column(options: ['default' => 0])]
    private int $pierceCount = 0;

    #[ORM\Column(options: ['default' => 0])]
    private float $areaOfEffect = 0;

    #[ORM\Column(type: 'text')]
    private string $maxUpgradeDescription;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $spritePath = null;

    /** @var Collection<int, Character> */
    #[ORM\OneToMany(targetEntity: Character::class, mappedBy: 'startingWeapon')]
    private Collection $startingCharacters;

    public function __construct(
        WeaponCode $code,
        string $name,
        string $description,
        string $attackType,
        float $baseDamage,
        float $attackInterval,
        string $maxUpgradeDescription,
        float $range = 0,
        int $projectileCount = 1,
        int $pierceCount = 0,
        float $areaOfEffect = 0,
    ) {
        parent::__construct($name, $description);
        $this->code = $code;
        $this->attackType = $attackType;
        $this->baseDamage = $baseDamage;
        $this->attackInterval = $attackInterval;
        $this->maxUpgradeDescription = $maxUpgradeDescription;
        $this->range = $range;
        $this->projectileCount = $projectileCount;
        $this->pierceCount = $pierceCount;
        $this->areaOfEffect = $areaOfEffect;
        $this->startingCharacters = new ArrayCollection();
    }

    public function getCode(): WeaponCode { return $this->code; }
    public function getName(): string { return $this->name; }
    public function getDescription(): string { return $this->description; }
    public function getAttackType(): string { return $this->attackType; }
    public function getBaseDamage(): float { return $this->baseDamage; }
    public function getAttackInterval(): float { return $this->attackInterval; }
    public function getRange(): float { return $this->range; }
    public function getProjectileCount(): int { return $this->projectileCount; }
    public function getPierceCount(): int { return $this->pierceCount; }
    public function getAreaOfEffect(): float { return $this->areaOfEffect; }
    public function getMaxLevel(): int { return $this->maxLevel; }
    public function getMaxUpgradeDescription(): string { return $this->maxUpgradeDescription; }
}
