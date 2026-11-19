<?php

declare(strict_types=1);

namespace App\DTO;

final readonly class GameConfiguration implements \JsonSerializable
{
    /** @param list<array<string, int|float|string>> $weapons @param list<array<string, int|float|string>> $powerUps @param array<string, int|float|string> $player */
    public function __construct(public array $player, public array $weapons, public array $powerUps)
    {
    }

    public function jsonSerialize(): array
    {
        return ['player' => $this->player, 'weapons' => $this->weapons, 'powerUps' => $this->powerUps];
    }
}
