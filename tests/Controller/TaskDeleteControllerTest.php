<?php

namespace App\tests\Controller;

use App\Entity\Task;
use App\DataFixtures\AppFixtures;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use App\Tests\Controller\ConnectUserTrait;
use Symfony\Component\HttpFoundation\Response;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class TaskDeleteControllerTest extends WebTestCase
{
    use FixturesTrait;
    use ConnectUserTrait;

    public function testTaskAnonymousDeleteByAdmin()
    {
        $client = static::createClient();
        $this->loadFixtures([AppFixtures::class]);
        $client= $this->userConnexion($client, 'admin');
        $crawler = $client->request('GET', '/tasks/list/0');
        $form = $crawler->selectButton('Supprimer')->form();
        $client->submit($form);
        $client->followRedirect();
        $this->assertSelectorExists('.alert.alert-success');
    }

    public function testTaskDeleteByOwner()
    {
        $client = static::createClient();
        $this->loadFixtures([AppFixtures::class]);
        $client= $this->userConnexion($client, 'essai');
        $crawler = $client->request('GET', '/tasks/list/0');
        $form = $crawler->selectButton('Supprimer')->form();
        $client->submit($form);
        $client->followRedirect();
        $this->assertSelectorTextContains('.alert.alert-success', "Superbe !");
    }

    public function testBadTokenInDeleteForm()
    {
        $client = static::createClient();
        $this->loadFixtures([AppFixtures::class]);
        $client= $this->userConnexion($client, 'essai');
        $crawler = $client->request('GET', '/tasks/list/0');
        $form = $crawler->selectButton('Supprimer')->form();
        $form['token'] = 'John Doe';
        $client->submit($form);
        $client->followRedirect();
        $this->assertSelectorTextContains('.alert.alert-danger', 'La tâche n\'a pas été supprimée.');
    }
}
