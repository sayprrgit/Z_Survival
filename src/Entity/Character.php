<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\CharacterCode;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Repository\CharacterRepository::class)]
#[ORM\Table(name: 'characters')]
final class Character extends AbstractCombatantCatalogItem
{
    #[ORM\Column(enumType: CharacterCode::class, unique: true)]
    private CharacterCode $code;

    #[ORM\Column(type: 'text')]
    private string $passiveDescription;

    #[ORM\Column(options: ['default' => 0])]
    private float $damageBonus = 0;

    #[ORM\Column(options: ['default' => 0])]
    private float $attackSpeedBonus = 0;

    #[ORM\Column(options: ['default' => 0])]
    private float $movementSpeedBonus = 0;

    #[ORM\Column(options: ['default' => 0])]
    private float $criticalChanceBonus = 0;

    #[ORM\Column(options: ['default' => 0])]
    private float $damageReduction = 0;

    #[ORM\Column(options: ['default' => 0])]
    private float $healthRegeneration = 0;

    #[ORM\Column(options: ['default' => 0])]
    private float $healingEffectivenessBonus = 0;

    #[ORM\ManyToOne(inversedBy: 'startingCharacters')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private Weapon $startingWeapon;

    #[ORM\Column(options: ['default' => false])]
    private bool $availableByDefault = false;

    #[ORM\Column(options: ['default' => 0])]
    private int $unlockCost = 0;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $unlockDescription = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $portraitPath = null;

    /** @var Collection<int, Run> */
    #[ORM\OneToMany(targetEntity: Run::class, mappedBy: 'character')]
    private Collection $runs;

    /** @var Collection<int, UserCharacter> */
    #[ORM\OneToMany(targetEntity: UserCharacter::class, mappedBy: 'character')]
    private Collection $userCharacters;

    public function __construct(
        CharacterCode $code,
        string $name,
        string $description,
        string $passiveDescription,
        int $baseHealth,
        Weapon $startingWeapon,
        bool $availableByDefault = false,
        int $unlockCost = 0,
        ?string $unlockDescription = null,
        float $damageBonus = 0,
        float $attackSpeedBonus = 0,
        float $movementSpeedBonus = 0,
        float $criticalChanceBonus = 0,
        float $damageReduction = 0,
        float $healthRegeneration = 0,
        float $healingEffectivenessBonus = 0,
    ) {
        parent::__construct($name, $description, $baseHealth);
        $this->code = $code;
        $this->passiveDescription = $passiveDescription;
        $this->startingWeapon = $startingWeapon;
        $this->availableByDefault = $availableByDefault;
        $this->unlockCost = $unlockCost;
        $this->unlockDescription = $unlockDescription;
        $this->damageBonus = $damageBonus;
        $this->attackSpeedBonus = $attackSpeedBonus;
        $this->movementSpeedBonus = $movementSpeedBonus;
        $this->criticalChanceBonus = $criticalChanceBonus;
        $this->damageReduction = $damageReduction;
        $this->healthRegeneration = $healthRegeneration;
        $this->healingEffectivenessBonus = $healingEffectivenessBonus;
        $this->runs = new ArrayCollection();
        $this->userCharacters = new ArrayCollection();
    }

    public function getCode(): CharacterCode
    {
        return $this->code;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getBaseHealth(): int
    {
        return $this->baseHealth;
    }

    public function getPassiveDescription(): string
    {
        return $this->passiveDescription;
    }

    public function getStartingWeapon(): Weapon
    {
        return $this->startingWeapon;
    }

    public function isAvailableByDefault(): bool
    {
        return $this->availableByDefault;
    }

    public function getUnlockCost(): int
    {
        return $this->unlockCost;
    }

    public function getDamageBonus(): float
    {
        return $this->damageBonus;
    }

    public function getAttackSpeedBonus(): float
    {
        return $this->attackSpeedBonus;
    }

    public function getMovementSpeedBonus(): float
    {
        return $this->movementSpeedBonus;
    }

    public function getCriticalChanceBonus(): float
    {
        return $this->criticalChanceBonus;
    }

    public function getDamageReduction(): float
    {
        return $this->damageReduction;
    }

    public function getHealthRegeneration(): float
    {
        return $this->healthRegeneration;
    }

    public function getHealingEffectivenessBonus(): float
    {
        return $this->healingEffectivenessBonus;
    }
}
