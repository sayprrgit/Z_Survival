<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'user_talents', uniqueConstraints: [new ORM\UniqueConstraint(name: 'uniq_user_talent', columns: ['user_id', 'talent_id'])])]
final class UserTalent extends AbstractUserAcquisition
{
    #[ORM\ManyToOne(inversedBy: 'userTalents')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Talent $talent;

    public function __construct(User $user, Talent $talent)
    {
        parent::__construct($user);
        $this->talent = $talent;
    }
}
