<?php

namespace App\tests\Controller;

use App\DataFixtures\AppFixtures;
use App\Repository\UserRepository;
use App\Tests\Controller\ConnectUserTrait;
use Symfony\Component\HttpFoundation\Response;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserListControllerTest extends WebTestCase
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
}
