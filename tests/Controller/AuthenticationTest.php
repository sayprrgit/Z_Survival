<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\DataFixtures\GameCatalogFixtures;
use App\Entity\User;
use App\Enum\RunStatus;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class AuthenticationTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->client->disableReboot();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->getConfiguration()->setUseSchemaEditorAPI(true);
        $metadata = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($entityManager);
        if ($entityManager->getConnection()->createSchemaManager()->listTableNames() !== []) {
            $schemaTool->dropSchema($metadata);
        }
        $schemaTool->createSchema($metadata);
        (new \Doctrine\Common\DataFixtures\Executor\ORMExecutor($entityManager))->execute([new GameCatalogFixtures()], append: true);
    }

    public function testRegistrationPersistsAHashedUser(): void
    {
        $crawler = $this->client->request('GET', '/register');
        $form = $crawler->selectButton('Créer mon compte')->form([
            'registration_form[email]' => 'Survivor@Example.test',
            'registration_form[plainPassword][first]' => 'strong-password-123',
            'registration_form[plainPassword][second]' => 'strong-password-123',
        ]);
        $this->client->submit($form);

        self::assertResponseRedirects('/login');
        $user = static::getContainer()->get(EntityManagerInterface::class)->getRepository(User::class)->findOneBy(['email' => 'survivor@example.test']);
        self::assertInstanceOf(User::class, $user);
        self::assertNotSame('strong-password-123', $user->getPassword());
        self::assertTrue(static::getContainer()->get(UserPasswordHasherInterface::class)->isPasswordValid($user, 'strong-password-123'));

        $crawler = $this->client->request('GET', '/register');
        $duplicateForm = $crawler->selectButton('Créer mon compte')->form([
            'registration_form[email]' => 'survivor@example.test',
            'registration_form[plainPassword][first]' => 'another-password-123',
            'registration_form[plainPassword][second]' => 'another-password-123',
        ]);
        $this->client->submit($duplicateForm);
        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('.auth-panel', 'possède déjà un compte');
    }

    public function testLoginAuthenticatesAndProtectsTheGameRoute(): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $user = new User('login@example.test', '');
        $user->setPassword(static::getContainer()->get(UserPasswordHasherInterface::class)->hashPassword($user, 'strong-password-123'));
        $entityManager->persist($user);
        $entityManager->flush();

        $crawler = $this->client->request('GET', '/login');
        $badCredentialsForm = $crawler->filter('form.auth-form')->form([
            'email' => 'login@example.test',
            'password' => 'incorrect-password',
        ]);
        $this->client->submit($badCredentialsForm);
        self::assertResponseRedirects('/login');

        $crawler = $this->client->request('GET', '/login');
        self::assertSelectorTextContains('.flash-warning', 'incorrect');
        self::assertResponseIsSuccessful();
        $form = $crawler->filter('form.auth-form')->form([
            'email' => 'login@example.test',
            'password' => 'strong-password-123',
        ]);
        $this->client->submit($form);

        self::assertResponseRedirects('/');
        $this->client->request('GET', '/game');
        self::assertResponseRedirects('/characters');

        $crawler = $this->client->request('GET', '/');
        $logoutForm = $crawler->filter('form.nav-logout')->form();
        $this->client->submit($logoutForm);
        self::assertResponseRedirects('/');
        $this->client->request('GET', '/game');
        self::assertResponseRedirects('/login');
    }

    public function testRunEndpointsPersistTheResultAndGrantRewardsOnlyOnce(): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $user = new User('runner@example.test', '');
        $user->setPassword(static::getContainer()->get(UserPasswordHasherInterface::class)->hashPassword($user, 'strong-password-123'));
        $entityManager->persist($user);
        $entityManager->flush();
        $this->client->loginUser($user);

        $crawler = $this->client->request('GET', '/');
        $selectForm = $crawler->filter('form[action="/characters/mason/select"]')->form();
        $this->client->submit($selectForm);
        self::assertResponseRedirects('/game');

        $crawler = $this->client->request('GET', '/game');
        $csrfStart = $crawler->filter('[data-game-root]')->attr('data-csrf-start');
        $csrfFinish = $crawler->filter('[data-game-root]')->attr('data-csrf-finish');
        $this->client->jsonRequest('POST', '/api/runs/start', ['character' => 'mason', 'difficulty' => 'normal'], ['HTTP_X_CSRF_TOKEN' => $csrfStart]);
        self::assertResponseStatusCodeSame(201);
        $runId = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR)['id'];

        $finishPayload = ['survivalSeconds' => 0, 'reachedLevel' => 1, 'kills' => 0, 'result' => 'defeat'];
        $this->client->jsonRequest('POST', sprintf('/api/runs/%d/finish', $runId), $finishPayload, ['HTTP_X_CSRF_TOKEN' => $csrfFinish]);
        self::assertResponseIsSuccessful();
        $summary = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame(RunStatus::RewardsGranted->value, $summary['status']);

        $this->client->jsonRequest('POST', sprintf('/api/runs/%d/finish', $runId), $finishPayload, ['HTTP_X_CSRF_TOKEN' => $csrfFinish]);
        self::assertResponseIsSuccessful();
        $userAfterFinish = $entityManager->find(User::class, $user->getId());
        self::assertSame(1, $userAfterFinish->getTotalRuns());

        $this->client->request('GET', '/api/runs?page=1&limit=10');
        $history = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame(1, $history['total']);
        self::assertSame($runId, $history['items'][0]['id']);
        self::assertSame('defeat', $history['items'][0]['result']);
    }

    public function testUnlocksTalentsProfileAndGameStatsUsePersistedAccountData(): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $user = new User('progression@example.test', '');
        $user->setPassword(static::getContainer()->get(UserPasswordHasherInterface::class)->hashPassword($user, 'strong-password-123'));
        $user->recordRun(1000, 120, 20, false);
        $entityManager->persist($user);
        $entityManager->flush();
        $this->client->loginUser($user);
        $crawler = $this->client->request('GET', '/');
        $lockedSelection = $crawler->filter('form[action="/characters/raven/select"]')->form();
        $this->client->submit($lockedSelection);
        self::assertResponseStatusCodeSame(403);

        $crawler = $this->client->request('GET', '/');
        $unlockToken = $crawler->filter('meta[name="csrf-unlock-character"]')->attr('content');
        $this->client->jsonRequest('POST', '/api/characters/raven/unlock', [], ['HTTP_X_CSRF_TOKEN' => $unlockToken]);
        self::assertResponseIsSuccessful();
        self::assertSame(750, json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR)['scrapBalance']);
        $this->client->jsonRequest('POST', '/api/characters/raven/unlock', [], ['HTTP_X_CSRF_TOKEN' => $unlockToken]);
        self::assertResponseIsSuccessful();
        self::assertTrue(json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR)['alreadyUnlocked']);

        $talentToken = $crawler->filter('meta[name="csrf-purchase-talent"]')->attr('content');
        $this->client->jsonRequest('POST', '/api/talents/armor/purchase', [], ['HTTP_X_CSRF_TOKEN' => $talentToken]);
        self::assertResponseStatusCodeSame(400);
        foreach (['endurance', 'armor', 'power', 'cadence'] as $talentCode) {
            $this->client->jsonRequest('POST', sprintf('/api/talents/%s/purchase', $talentCode), [], ['HTTP_X_CSRF_TOKEN' => $talentToken]);
            self::assertResponseIsSuccessful();
        }
        $this->client->jsonRequest('POST', '/api/talents/endurance/purchase', [], ['HTTP_X_CSRF_TOKEN' => $talentToken]);
        self::assertTrue(json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR)['alreadyOwned']);
        $this->client->jsonRequest('POST', '/api/characters/boris/unlock', [], ['HTTP_X_CSRF_TOKEN' => $unlockToken]);
        self::assertResponseStatusCodeSame(400);

        $profile = $this->client->request('GET', '/api/profile');
        $profileData = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame(200, $this->client->getResponse()->getStatusCode());
        self::assertSame(200, $profileData['scrap']);
        self::assertContains('raven', array_column(array_filter($profileData['characters'], static fn (array $item): bool => $item['unlocked']), 'code'));
        self::assertContains('cadence', array_column(array_filter($profileData['talents'], static fn (array $item): bool => $item['owned']), 'code'));

        $this->client->request('GET', '/api/runs?page=1&limit=10');
        $history = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame(0, $history['total']);

        $crawler = $this->client->request('GET', '/');
        $form = $crawler->filter('form[action="/characters/mason/select"]')->form();
        $this->client->submit($form);
        $crawler = $this->client->request('GET', '/game');
        $configuration = json_decode($crawler->filter('[data-game-config]')->text(), true, flags: JSON_THROW_ON_ERROR);
        self::assertCount(6, $configuration['enemies']);
        self::assertCount(2, array_filter($configuration['enemies'], static fn (array $enemy): bool => $enemy['isBoss']));
        self::assertCount(3, $configuration['difficulties']);
        self::assertEqualsWithDelta(0.2, $configuration['player']['damageBonus'], 0.000001);
        self::assertEqualsWithDelta(0.15, $configuration['player']['attackSpeedBonus'], 0.000001);
        self::assertEqualsWithDelta(0.03, $configuration['player']['damageReduction'], 0.000001);
    }
}
