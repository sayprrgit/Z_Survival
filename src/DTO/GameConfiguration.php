<?php

declare(strict_types=1);

namespace App\DTO;

final readonly class GameConfiguration implements \JsonSerializable
{
    /**
     * @param array<string, int|float|string> $player
     * @param list<array<string, int|float|string|bool|null>> $weapons
     * @param list<array<string, int|float|string|bool|null>> $powerUps
     * @param list<array<string, int|float|string|bool|null>> $enemies
     * @param list<array<string, int|float|string|bool|null>> $difficulties
     */
    public function __construct(
        public array $player,
        public array $weapons,
        public array $powerUps,
        public array $enemies,
        public array $difficulties,
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'player' => $this->player,
            'weapons' => $this->weapons,
            'powerUps' => $this->powerUps,
            'enemies' => $this->enemies,
            'difficulties' => $this->difficulties,
        ];
    }
}
