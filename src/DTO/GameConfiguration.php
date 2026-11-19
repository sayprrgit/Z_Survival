<?php

declare(strict_types=1);

namespace App\DTO;

final readonly class GameConfiguration implements \JsonSerializable
{
    /** @param list<array<string, int|float|string|bool>> $weapons @param list<array<string, int|float|string|bool>> $powerUps @param list<array<string, int|float|string|bool>> $enemies @param list<array<string, int|float|string>> $difficulties @param array<string, int|float|string> $player */
    public function __construct(public array $player, public array $weapons, public array $powerUps, public array $enemies, public array $difficulties, public string $difficulty = 'normal')
    {
    }

    public function jsonSerialize(): array
    {
        return ['player' => $this->player, 'weapons' => $this->weapons, 'powerUps' => $this->powerUps, 'enemies' => $this->enemies, 'difficulties' => $this->difficulties, 'difficulty' => $this->difficulty];
    }
}
