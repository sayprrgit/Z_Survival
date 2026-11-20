<?php

declare(strict_types=1);

namespace App\DTO;

final readonly class CharacterUnlockReceipt
{
    public function __construct(public string $characterCode, public bool $alreadyUnlocked, public int $costPaid, public int $scrapBalance)
    {
    }
}
