<?php

declare(strict_types=1);

namespace App\Game\Reward;

final readonly class Reward
{
    public function __construct(public int $scrap)
    {
    }
}
