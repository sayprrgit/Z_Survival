<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/** Shared health definition for playable characters and enemy types. */
#[ORM\MappedSuperclass]
abstract class AbstractCombatantCatalogItem extends AbstractCatalogItem
{
    #[ORM\Column]
    protected int $baseHealth;

    protected function __construct(string $name, string $description, int $baseHealth)
    {
        parent::__construct($name, $description);
        $this->baseHealth = $baseHealth;
    }

    public function getBaseHealth(): int
    {
        return $this->baseHealth;
    }
}
