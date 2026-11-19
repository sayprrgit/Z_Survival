<?php

declare(strict_types=1);

namespace App\DTO;

final readonly class PlayerProfile
{
    /** @param list<CharacterCatalogItem> $characters @param list<TalentCatalogItem> $talents */
    public function __construct(
        public int $id,
        public string $email,
        public int $scrap,
        public int $totalRuns,
        public int $totalVictories,
        public int $totalSurvivalSeconds,
        public int $bestSurvivalSeconds,
        public int $totalKills,
        public array $characters,
        public array $talents,
    ) {
    }
}
