<?php

declare(strict_types=1);

namespace App\DTO;

final readonly class RunHistoryPage
{
    /** @param list<RunHistoryItem> $items */
    public function __construct(public int $page, public int $limit, public int $total, public array $items)
    {
    }
}
