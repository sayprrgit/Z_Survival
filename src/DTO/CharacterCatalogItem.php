<?php

declare(strict_types=1);

namespace App\DTO;

final readonly class CharacterCatalogItem
{
    public function __construct(
        public string $code,
        public string $name,
        public string $description,
        public string $passiveDescription,
        public int $baseHealth,
        public string $startingWeapon,
        public bool $unlocked,
        public int $unlockCost,
    ) {
    }
}
