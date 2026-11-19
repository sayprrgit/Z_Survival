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
    }
}
