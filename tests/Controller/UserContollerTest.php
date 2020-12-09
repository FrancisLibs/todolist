<?php

namespace App\tests\Controller;

use App\DataFixtures\AppFixtures;
use App\Repository\UserRepository;
use App\Tests\Controller\ConnectUserTrait;
use Symfony\Component\HttpFoundation\Response;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    use FixturesTrait;
    use ConnectUserTrait;
    
    public function testUsersListIsRestricted()
    {
        $client = self::createClient();
        $this->loadFixtures([AppFixtures::class]);
        $client->request('GET', '/users');
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $client= $this->userConnexion($client, 'essai');
        $client->request('GET', '/users');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testUsersListIsRestrictedToAdmin()
    {
        $client = self::createClient();
        $this->loadFixtures([AppFixtures::class]);
        $client= $this->userConnexion($client, 'admin');
        $client->request('GET', '/users');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('h1', 'Liste des utilisateurs');
    }

    public function testUserCreateIsUnrestricted()
    {
        $client = self::createClient();
        $this->loadFixtures([AppFixtures::class]);
        $client->request('GET', '/users/create');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('button', 'Ajouter');
    }

    public function testUserCreateDisplayForm()
    {
        $client = static::createClient();
        $this->loadFixtures([AppFixtures::class]);
        $client->request('GET', '/users/create');
        $this->assertSelectorTextContains('button', 'Ajouter');
    }

    public function testUserCreate()
    {
        $client = static::createClient();
        $this->loadFixtures([AppFixtures::class]);
        $client->request('GET', '/users/create');
        // Remplissage formulaire
        $client->submitForm('Ajouter', [
            'user[username]'    => 'TestUsername',
            'user[email]' => 'testEmail@gmail.com',
            'user[password]' => [
                'first' => 'password', 
                'second' => 'password'
            ],
        ]);
        $client->followRedirect();
        $this->assertSelectorTextContains('h1', 'Bienvenue sur Todo List, l\'application vous permettant de gérer l\'ensemble de vos tâches sans effort !');
        // Test insert in database
        $kernel = self::bootKernel();
        $em = $kernel->getContainer()->get('doctrine.orm.entity_manager');
        $query = $em->createQuery('SELECT count(u.id) from App\Entity\User u WHERE u.username = :username AND u.email = :email');
        $query->setParameter('username', 'TestUsername');
        $query->setParameter('email', 'testEmail@gmail.com');
        $this->assertTrue(0 < $query->getSingleScalarResult());
    }

    public function testUserEditIsRestricted()
    {
        $client = static::createClient();
        $this->loadFixtures([AppFixtures::class]);
        $client->request('GET', '/users/1/edit');
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $client= $this->userConnexion($client, 'essai');
        $client->request('GET', '/users/1/edit');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testUserEditAllowWithAdmin()
    {
        $client = static::createClient();
        $this->loadFixtures([AppFixtures::class]);
        $client= $this->userConnexion($client, 'admin');
        $client->request('GET', '/users/1/edit');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testUserEditAdmin()
    {
        $client = static::createClient();
        $this->loadFixtures([AppFixtures::class]);
        $userRepository = static::$container->get(UserRepository::class);
        /** @var User $userTest */
        $user = $userRepository->find(1);
        $client= $this->userConnexion($client, 'admin');
        $client->request('GET', '/users/' . $user->getId() . '/edit');
        // Submit form
        $client->submitForm('Modifier', [
            'user_edit[username]'    => 'TestEditUsername',
            'user_edit[email]' => 'testEditEmail@gmail.com',
        ]);
        $client->followRedirect();
        $this->assertSelectorExists('.alert.alert-success');
    }
}