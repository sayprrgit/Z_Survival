<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\TalentBranch;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Repository\TalentRepository::class)]
#[ORM\Table(name: 'talents')]
final class Talent extends AbstractCatalogItem
{
    #[ORM\Column(length: 100, unique: true)]
    private string $code;

    #[ORM\Column(enumType: TalentBranch::class)]
    private TalentBranch $branch;

    #[ORM\Column]
    private int $tier;

    #[ORM\Column]
    private int $cost;

    #[ORM\Column(length: 50)]
    private string $effectType;

    #[ORM\Column]
    private float $effectValue;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'children')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Talent $parent = null;

    /** @var Collection<int, Talent> */
    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'parent')]
    private Collection $children;

    /** @var Collection<int, UserTalent> */
    #[ORM\OneToMany(targetEntity: UserTalent::class, mappedBy: 'talent')]
    private Collection $userTalents;

    public function __construct(string $code, string $name, string $description, TalentBranch $branch, int $tier, int $cost, string $effectType, float $effectValue)
    {
        parent::__construct($name, $description);
        $this->code = $code;
        $this->branch = $branch;
        $this->tier = $tier;
        $this->cost = $cost;
        $this->effectType = $effectType;
        $this->effectValue = $effectValue;
        $this->children = new ArrayCollection();
        $this->userTalents = new ArrayCollection();
    }

    public function setParent(?self $parent): void
    {
        $this->parent = $parent;
    }
}
