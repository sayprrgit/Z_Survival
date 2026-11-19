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

    public function __construct(CharacterCode $code, string $name, string $description, string $passiveDescription, int $baseHealth, Weapon $startingWeapon)
    {
        parent::__construct($name, $description, $baseHealth);
        $this->code = $code;
        $this->passiveDescription = $passiveDescription;
        $this->startingWeapon = $startingWeapon;
        $this->runs = new ArrayCollection();
        $this->userCharacters = new ArrayCollection();
    }
}
