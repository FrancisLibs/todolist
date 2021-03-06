<?php

namespace App\tests\Controller;

use App\DataFixtures\AppFixtures;
use App\Repository\UserRepository;
use App\Tests\Controller\ConnectUserTrait;
use Symfony\Component\HttpFoundation\Response;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserDeleteControllerTest extends WebTestCase
{
    use FixturesTrait;
    use ConnectUserTrait;

    /**
     * Creation of user by everyone
     *
     * @return void
     */
    public function testUserDeleteIsMethodRestricted()
    {
        $client = self::createClient();
        // Without connexion
        $this->loadFixtures([AppFixtures::class]);
        $client->request('GET', '/user/1/delete');
        $this->assertResponseStatusCodeSame(Response::HTTP_METHOD_NOT_ALLOWED);

        // With User connected
        $client= $this->userConnexion($client, 'essai');
        $client->request('GET', '/user/1/delete');
        $this->assertResponseStatusCodeSame(Response::HTTP_METHOD_NOT_ALLOWED);

        // With admin connected
        $client= $this->userConnexion($client, 'admin');
        $client->request('GET', '/user/1/delete');
        $this->assertResponseStatusCodeSame(Response::HTTP_METHOD_NOT_ALLOWED);
    }

    public function testUserDelete()
    {
        $client = self::createClient();
        $this->loadFixtures([AppFixtures::class]);
        $client= $this->userConnexion($client, 'admin');
        $crawler = $client->request('GET', '/users');
        $form = $crawler->selectButton('Supprimer')->form();
        $client->submit($form);
        $client->followRedirect();
        $this->assertSelectorTextContains('.alert.alert-success', "Superbe !");
    }

    public function testUserDeleteHimself()
    {
        $client = self::createClient();
        $this->loadFixtures([AppFixtures::class]);
        $client= $this->userConnexion($client, 'admin');
        $crawler = $client->request('GET', '/users');
        //$form = $crawler->selectButton('tody>Supprimer')->form();
        $form=$crawler->filter('form[action="/user/3/delete"]')->form();
        $client->submit($form);
        $client->followRedirect();
        $this->assertSelectorTextContains('.alert.alert-danger', "Oops !");
    }

    public function testUserDeleteWithBadToken()
    {
        $client = self::createClient();
        $this->loadFixtures([AppFixtures::class]);
        $client= $this->userConnexion($client, 'admin');
        $crawler = $client->request('GET', '/users');
        $form = $crawler->selectButton('Supprimer')->form();
        $form['token'] = 'BadToken';
        $client->submit($form);
        $client->followRedirect();
        $this->assertSelectorTextContains('.alert.alert-danger', 'L\'utilisateur n\'a pas été supprimé.');
    }
}
