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

    /**
     * The display of users list is constrained
     *
     * @return void
     */
    public function testUsersListIsRestricted()
    {
        $client = self::createClient();
        $this->loadFixtures([AppFixtures::class]);
        
        // Without connection
        $client->request('GET', '/users');
        $client->followRedirect();
        $this->assertSelectorTextContains('button', "Se connecter");

        // With a no admin user
        $client= $this->userConnexion($client, 'essai');
        $client->request('GET', '/users');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        // With an administrator
        $client= $this->userConnexion($client, 'admin');
        $client->request('GET', '/users');
        $this->assertResponseIsSuccessful();
    }

    /**
     * Creation of user by everyone
     *
     * @return void
     */
    public function testUserCreateIsUnrestricted()
    {
        $client = self::createClient();
        $this->loadFixtures([AppFixtures::class]);
        $client->request('GET', '/users/create');
        $this->assertResponseIsSuccessful();
    }

    /**
     * Display the user creation form
     *
     * @return void
     */
    public function testUserCreateDisplayForm()
    {
        $client = static::createClient();
        $client->request('GET', '/users/create');
        $this->assertSelectorTextContains('button', 'Créer');
    }

    /**
     * Creation of user
     *
     * @return void
     */
    public function testUserCreate()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/users/create');
        $buttonCrawlerNode = $crawler->selectButton('Créer');
        $form = $buttonCrawlerNode->form([
            'user[username]'    => 'TestUsername',
            'user[email]' => 'testEmail@gmail.com',
            'user[password]' => [
                'first' => 'password', 
                'second' => 'password'
            ],
        ]);
        $client->submit($form);
        $client->followRedirect();
        $this->assertSelectorTextContains('.alert.alert-success', "L'utilisateur a bien été ajouté.");
        // Test insert in database
        $kernel = self::bootKernel();
        $em = $kernel->getContainer()->get('doctrine.orm.entity_manager');
        $query = $em->createQuery('SELECT count(u.id) from App\Entity\User u WHERE u.username = :username AND u.email = :email');
        $query->setParameter('username', 'TestUsername');
        $query->setParameter('email', 'testEmail@gmail.com');
        $this->assertTrue(0 < $query->getSingleScalarResult());
    }

    /**
     * The edition of a user is constrained
     *
     * @return void
     */
    public function testUserEditIsRestricted()
    {
        $client = static::createClient();
        $this->loadFixtures([AppFixtures::class]);
        
        // Wirh no connection
        $client->request('GET', '/users/1/edit');
        $client->followRedirect();
        $this->assertSelectorTextContains('button', "Se connecter");
        
        // With a no admin user
        $client= $this->userConnexion($client, 'essai');
        $client->request('GET', '/users/1/edit');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
       
        // With an administrator
        $client= $this->userConnexion($client, 'admin');
        $client->request('GET', '/users/1/edit');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * Test of modification of user by admin
     *
     * @return void
     */
    public function testUserEditAdmin()
    {
        $client = static::createClient();
        $this->loadFixtures([AppFixtures::class]);
        $userRepository = static::$container->get(UserRepository::class);
        
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