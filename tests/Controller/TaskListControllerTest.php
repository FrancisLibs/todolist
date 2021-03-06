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

class TaskListControllerTest extends WebTestCase
{
    use FixturesTrait;
    use ConnectUserTrait;

    /**
    * @dataProvider provideUrls
    */
    public function testTaskListIsRestricted($url)
    {
        $client = self::createClient();
        $this->loadFixtures([AppFixtures::class]);
        // Without connexion
        $client->request('GET', $url);
        $this->assertResponseRedirects('/login');
        // With a connected user
        $client= $this->userConnexion($client, 'user');
        $client->request('GET', $url);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('.btn.btn-info', 'Créer une tâche');
    }

    public function provideUrls()
    {
        return [
            ['/tasks/list/0'],
            ['/tasks/list/1']
        ];
    }

    public function testUndoneTaskList()
    {
        $client = static::createClient();
        $this->loadFixtures([AppFixtures::class]);
        $client= $this->userConnexion($client, 'essai');
        $client->request('GET', '/tasks/list/0');
        $this->assertSelectorExists('.bi.bi-plus-circle');
    }

    public function testDoneTaskList()
    {
        $client = static::createClient();
        $this->loadFixtures([AppFixtures::class]);
        $client= $this->userConnexion($client, 'essai');
        $client->request('GET', '/tasks/list/1');
        $this->assertSelectorExists('.bi.bi-plus-circle');
    }

    public function testAdminDoneTaskList()
    {
        $client = static::createClient();
        $this->loadFixtures([AppFixtures::class]);
        $client= $this->userConnexion($client, 'admin');
        $client->request('GET', '/tasks/list/1');
        $this->assertSelectorExists('.bi.bi-check-circle');
    }

    public function testAdminUndoneTaskList()
    {
        $client = static::createClient();
        $this->loadFixtures([AppFixtures::class]);
        $client= $this->userConnexion($client, 'admin');
        $client->request('GET', '/tasks/list/1');
        $this->assertSelectorExists('.bi.bi-check-circle');
    }
}
