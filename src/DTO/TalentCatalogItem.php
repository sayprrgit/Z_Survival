<?php

declare(strict_types=1);

namespace App\DTO;

final readonly class TalentCatalogItem
{
    public function __construct(
        public string $code,
        public string $name,
        public string $description,
        public string $branch,
        public int $tier,
        public int $cost,
        public string $effectType,
        public float $effectValue,
        public ?string $parentCode,
        public bool $owned,
        public bool $canPurchase,
    ) {
    }
}
