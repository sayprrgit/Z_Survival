<?php

declare(strict_types=1);

namespace App\Tests\DataFixtures;

use App\DataFixtures\GameCatalogFixtures;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\UnderscoreNamingStrategy;
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
        $configuration->setNamingStrategy(new UnderscoreNamingStrategy());
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

    public function testLoadsTheCompleteCatalogIdempotently(): void
    {
        $fixture = new GameCatalogFixtures();
        (new ORMExecutor($this->entityManager))->execute([$fixture, $fixture], append: true);

        self::assertSame(4, $this->countRows('characters'));
        self::assertSame(6, $this->countRows('weapons'));
        self::assertSame(8, $this->countRows('power_ups'));
        self::assertSame(6, $this->countRows('enemy_types'));
        self::assertSame(2, $this->countRows('enemy_types', 'is_boss = 1'));
        self::assertSame(15, $this->countRows('talents'));
        self::assertSame(3, $this->countRows('difficulties'));
        self::assertEqualsWithDelta(0.25, (float) $this->entityManager->getConnection()->fetchOne("SELECT healing_effectiveness_bonus FROM characters WHERE code = 'eva'"), 0.0001);
    }

    private function countRows(string $tableName, string $condition = '1 = 1'): int
    {
        return (int) $this->entityManager->getConnection()->fetchOne(sprintf('SELECT COUNT(*) FROM %s WHERE %s', $tableName, $condition));
    }
}
