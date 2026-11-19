<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'user_characters', uniqueConstraints: [new ORM\UniqueConstraint(name: 'uniq_user_character', columns: ['user_id', 'character_id'])])]
final class UserCharacter extends AbstractUserAcquisition
{
    #[ORM\ManyToOne(inversedBy: 'userCharacters')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Character $character;

    public function __construct(User $user, Character $character)
    {
        parent::__construct($user);
        $this->character = $character;
    }
}
