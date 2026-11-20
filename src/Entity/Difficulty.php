<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\DifficultyCode;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Repository\DifficultyRepository::class)]
#[ORM\Table(name: 'difficulties')]
final class Difficulty extends AbstractCatalogItem
{
    #[ORM\Column(enumType: DifficultyCode::class, unique: true)]
    private DifficultyCode $code;

    #[ORM\Column]
    private float $multiplier;

    #[ORM\Column]
    private int $sortOrder;

    /** @var Collection<int, Run> */
    #[ORM\OneToMany(targetEntity: Run::class, mappedBy: 'difficulty')]
    private Collection $runs;

    public function __construct(DifficultyCode $code, string $name, string $description, float $multiplier, int $sortOrder)
    {
        parent::__construct($name, $description);
        $this->code = $code;
        $this->multiplier = $multiplier;
        $this->sortOrder = $sortOrder;
        $this->runs = new ArrayCollection();
    }

    public function getCode(): DifficultyCode
    {
        return $this->code;
    }

    public function getMultiplier(): float
    {
        return $this->multiplier;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }
}
