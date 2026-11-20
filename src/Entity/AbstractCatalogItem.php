<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/** Common human-readable metadata for a configurable game-catalog item. */
#[ORM\MappedSuperclass]
abstract class AbstractCatalogItem extends AbstractEntity
{
    #[ORM\Column(length: 100)]
    protected string $name;

    #[ORM\Column(type: 'text')]
    protected string $description;

    protected function __construct(string $name, string $description)
    {
        $this->name = $name;
        $this->description = $description;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}
