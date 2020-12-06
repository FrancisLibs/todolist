<?php

namespace App\test\Controller;

use App\DataFixtures\AppFixtures;
use App\Tests\Controller\ConnectUserTrait;
use Symfony\Component\HttpFoundation\Response;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    use FixturesTrait;
    use ConnectUserTrait;

    public function testDisplayLogin()
    {
        $client = static::createClient();
        $client->request('GET', '/login');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('form');
        $this->assertSelectorNotExists('.alert.alert-danger');
    }

    public function testLoginPageIsUnrestricted()
    {
        $client = static::createClient();
        $client->request('GET', '/login');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testConnectWithBadCredentials()
    {
        $client = static::createClient();
        self::bootKernel();
        $this->loadFixtures([AppFixtures::class]);
        $crawler = $client->request('GET', '/login');
        $form = $crawler->selectButton("Se connecter")->form([
            'username'  =>  'essai',
            'password'  =>  'fakePassword',
        ]);

        $client->submit($form);
        $this->assertResponseRedirects('/login');
        $client->followRedirect();
        $this->assertSelectorExists('.alert.alert-danger');
    }

    public function testSuccessFullLogin()
    {
        $client = static::createClient();
        $this->loadFixtures([AppFixtures::class]);
        $csrfToken = $client->getContainer()->get('security.csrf.token_manager')->getToken('authenticate');
        $client->request('POST', '/login', [
            '_csrf_token' => $csrfToken,
            'username'  =>  'essai',
            'password'  =>  '000000',
        ]);
        $this->assertResponseRedirects('/login');
    }

    public function testLogout()
    {
        $client = static::createClient();
        $this->loadFixtures([AppFixtures::class]);
        $client= $this->userConnexion($client, 'essai'); 
        $client->request('GET', '/logout');
        $client->followRedirect();
        $this->assertSelectorExists('.btn.btn-success');
    }
}