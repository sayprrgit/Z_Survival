<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261120120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Synchronise les libellés du catalogue avec le dump de référence.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'UPDATE characters SET unlock_description = ? WHERE code = ?',
            ['Personnage déblocable avec de la ferraille (coût indicatif MVP).', 'raven'],
        );
        $this->addSql(
            'UPDATE difficulties SET description = ? WHERE code = ?',
            ['Le mode de difficulté de départ.', 'normal'],
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql(
            'UPDATE characters SET unlock_description = ? WHERE code = ?',
            ['Coût de déblocage indicatif, à équilibrer.', 'raven'],
        );
        $this->addSql(
            'UPDATE difficulties SET description = ? WHERE code = ?',
            ['Mode de difficulté du jeu.', 'normal'],
        );
    }
}
