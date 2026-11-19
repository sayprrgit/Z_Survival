<?php

declare(strict_types=1);

namespace App\Tests\DataFixtures;

use App\DataFixtures\GameCatalogFixtures;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class GameCatalogFixturesTest extends KernelTestCase
{
    private ?EntityManager $entityManager = null;

    protected function setUp(): void
    {
        parent::setUp();
        static::bootKernel();

        $configuration = ORMSetup::createAttributeMetadataConfig(
            [dirname(__DIR__, 2).'/src/Entity'],
            isDevMode: true,
        );
        $configuration->enableNativeLazyObjects(true);
        $configuration->setUseSchemaEditorAPI(true);
        $connection = DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ]);

        $this->entityManager = new EntityManager($connection, $configuration);
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        (new SchemaTool($this->entityManager))->createSchema($metadata);
    }

    protected function tearDown(): void
    {
        $this->entityManager?->close();
        $this->entityManager = null;

        parent::tearDown();
    }

    public function testLoadsTheMvpGameCatalog(): void
    {
        (new ORMExecutor($this->entityManager))->execute([new GameCatalogFixtures()], append: true);

        self::assertSame(2, $this->countRows('characters'));
        self::assertSame(3, $this->countRows('weapons'));
        self::assertSame(4, $this->countRows('power_ups'));
        self::assertSame(4, $this->countRows('enemy_types'));
        self::assertSame(9, $this->countRows('talents'));
        self::assertSame(1, $this->countRows('difficulties'));
    }

    private function countRows(string $tableName): int
    {
        return (int) $this->entityManager->getConnection()->fetchOne(sprintf('SELECT COUNT(*) FROM %s', $tableName));
    }
}
