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

class TaskToggleControllerTest extends WebTestCase
{
    use FixturesTrait;
    use ConnectUserTrait;

    public function testToggleTaskAdminWithAnonymousTask()
    {
        $client = static::createClient();
        $this->loadFixtures([AppFixtures::class]);
        $taskRepository = static::$container->get(TaskRepository::class);
        $client= $this->userConnexion($client, 'admin');
        $client->request('GET', '/tasks/9/toggle');
        $task = $taskRepository->find(9);
        $done = $task->isDone();
        $this->assertEquals($done, 0);
        $client->followRedirect();
        $this->assertSelectorTextContains('.alert.alert-success', 'Superbe !');
    }

    public function testToggleTaskAdminWithownersTask()
    {
        $client = static::createClient();
        $this->loadFixtures([AppFixtures::class]);
        $taskRepository = static::$container->get(TaskRepository::class);
        $client= $this->userConnexion($client, 'admin');
        $crawler = $client->request('GET', '/tasks/46/toggle');
        $client->followRedirect();
        $this->assertSelectorTextContains('.alert.alert-success', 'Superbe !');
    }

    public function testToggleTaskUserWithOwnersTask()
    {
        $client = static::createClient();
        $this->loadFixtures([AppFixtures::class]);
        $taskRepository = static::$container->get(TaskRepository::class);
        $client= $this->userConnexion($client, 'essai');
        $crawler = $client->request('GET', '/tasks/16/toggle');
        $client->followRedirect();
        $this->assertSelectorTextContains('.alert.alert-success', 'Superbe !');
    }

    public function testToggleTaskAUserWithUnownersTask()
    {
        $client = static::createClient();
        $this->loadFixtures([AppFixtures::class]);
        $taskRepository = static::$container->get(TaskRepository::class);
        $client= $this->userConnexion($client, 'essai');
        $crawler = $client->request('GET', '/tasks/15/toggle');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN); // Code 404
    }

    public function testToggleTaskWithNoConnection()
    {
        $client = static::createClient();
        $this->loadFixtures([AppFixtures::class]);
        $taskRepository = static::$container->get(TaskRepository::class);
        $this->loadFixtures([AppFixtures::class]);
        $crawler = $client->request('GET', '/tasks/16/toggle');
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND); // Code 302
    }
}
