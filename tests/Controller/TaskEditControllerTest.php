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

class TaskEditControllerTest extends WebTestCase
{
    use FixturesTrait;
    use ConnectUserTrait;

    public function testAccessTaskEditWithoutUser()
    {
        $client = static::createClient();
        $this->loadFixtures([AppFixtures::class]);
        $client= $this->userConnexion($client, 'essai');
        $client->request('GET', '/tasks/1/edit');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testAccessTaskEditAdmin()
    {
        $client = static::createClient();
        $this->loadFixtures([AppFixtures::class]);
        $client= $this->userConnexion($client, 'admin');
        $client->request('GET', '/tasks/1/edit');
        $this->assertResponseIsSuccessful();
    }
    
    public function testTaskEditAdmin()
    {
        $client = static::createClient();
        $this->loadFixtures([AppFixtures::class]);
        $taskRepository = static::$container->get(TaskRepository::class);
        /** @var Task $taskTest */
        $task = $taskRepository->find(1);
        $client= $this->userConnexion($client, 'admin');
        $client->request('GET', '/tasks/' . $task->getId() . '/edit');
        // Submit form
        $client->submitForm('Modifier', [
            'task[title]'    => 'TestTitle',
            'task[content]' => 'Test content',
        ]);
        $client->followRedirect();
        $this->assertSelectorExists('.alert.alert-success');
    }

    public function testRestrictedAccessTaskEdit()
    {
        $client = static::createClient();
        $this->loadFixtures([AppFixtures::class]);
        $taskRepository = static::$container->get(TaskRepository::class);
         /** @var Task $taskTest */
        $task = $taskRepository->find(1);
        $client= $this->userConnexion($client, 'essai');
        $client->request('GET', '/tasks/' . $task->getId() . '/edit');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testEditTaskByOwner()
    {
        $client = static::createClient();
        $this->loadFixtures([AppFixtures::class]);
        $client= $this->userConnexion($client, 'essai');
        $taskRepository = static::$container->get(TaskRepository::class);
        $task = $taskRepository->find(16);
        // Task with user's Id = 1 (essai)
        $client->request('GET', '/tasks/' . $task->getId() . '/edit');
        $client->submitForm('Modifier', [
            'task[title]'    => 'TestTitle',
            'task[content]' => 'Test content',
        ]);
        $client->followRedirect();
        $this->assertSelectorExists('.alert.alert-success');
    }
}
