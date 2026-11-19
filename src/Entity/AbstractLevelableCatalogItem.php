<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/** Catalog item that can be upgraded during a run. */
#[ORM\MappedSuperclass]
abstract class AbstractLevelableCatalogItem extends AbstractCatalogItem
{
    #[ORM\Column(options: ['default' => 5])]
    protected int $maxLevel = 5;
}
