<?php

namespace App\tests\Controller;

use App\DataFixtures\AppFixtures;
use App\Repository\UserRepository;
use App\Tests\Controller\ConnectUserTrait;
use Symfony\Component\HttpFoundation\Response;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserCreateControllerTest extends WebTestCase
{
    use FixturesTrait;
    use ConnectUserTrait;

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
    public function testUserCreateByVisitor()
    {
        $client = static::createClient();
        $this->loadFixtures([AppFixtures::class]);
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

    public function testUserCreateByAdmin()
    {
        $client = static::createClient();
        $this->loadFixtures([AppFixtures::class]);
        $client= $this->userConnexion($client, 'admin');
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
        $this->assertSelectorTextContains('h1', "Liste des utilisateurs");
        
       /* // Test insert in database
        $kernel = self::bootKernel();
        $em = $kernel->getContainer()->get('doctrine.orm.entity_manager');
        $query = $em->createQuery('SELECT count(u.id) from App\Entity\User u WHERE u.username = :username AND u.email = :email');
        $query->setParameter('username', 'TestUsername');
        $query->setParameter('email', 'testEmail@gmail.com');
        $this->assertTrue(0 < $query->getSingleScalarResult());*/
    }
}