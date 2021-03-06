<?php

namespace App\tests\Controller;

use App\DataFixtures\AppFixtures;
use App\Repository\UserRepository;
use App\Tests\Controller\ConnectUserTrait;
use Symfony\Component\HttpFoundation\Response;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserEditControllerTest extends WebTestCase
{
    use FixturesTrait;
    use ConnectUserTrait;

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
