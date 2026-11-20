<?php

declare(strict_types=1);

namespace App\Game\Character;

use App\Enum\CharacterCode;

final class CharacterFactory
{
    /** @return list<PlayableCharacter> */
    public function all(): array
    {
        return [new Mason(), new Raven(), new Boris(), new Eva()];
    }

    public function find(CharacterCode $code): PlayableCharacter
    {
        return match ($code) {
            CharacterCode::Mason => new Mason(),
            CharacterCode::Raven => new Raven(),
            CharacterCode::Boris => new Boris(),
            CharacterCode::Eva => new Eva(),
        };
    }

    public function findByValue(string $code): ?PlayableCharacter
    {
        $characterCode = CharacterCode::tryFrom($code);

        return $characterCode === null ? null : $this->find($characterCode);
    }
}
