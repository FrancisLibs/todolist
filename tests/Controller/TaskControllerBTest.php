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

class TaskControllerBTest extends WebTestCase
{
    use FixturesTrait;
    use ConnectUserTrait;

    public function testEditTaskByOwner()
    {
        $client = static::createClient();
        $this->loadFixtures([AppFixtures::class]);
        $client= $this->userConnexion($client, 'essai');
        $taskRepository = static::$container->get(TaskRepository::class);
         /** @var Task $taskTest */
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

    public function testToggleNullTaskByAdmin()
    {
        $client = static::createClient();
        $this->loadFixtures([AppFixtures::class]);
        $client= $this->userConnexion($client, 'admin');
        $crawler = $client->request('GET', '/tasks_undone');
        $form = $crawler->selectButton('Marquer comme faite')->form();
        $client->submit($form);
        $client->followRedirect();
        $this->assertSelectorExists('.alert.alert-success');
    }

    public function testToggleTaskByUser()
    {
        $client = static::createClient();
        $this->loadFixtures([AppFixtures::class]);
        $client= $this->userConnexion($client, 'essai');
        $crawler = $client->request('GET', '/tasks_undone');
        $form = $crawler->selectButton('Marquer comme faite')->form();
        $client->submit($form);
        $client->followRedirect();
        $this->assertSelectorExists('.alert.alert-success');
    }

    public function testTaskAnonymousDeleteByAdmin()
    {
        $client = static::createClient();
        $this->loadFixtures([AppFixtures::class]);
        $client= $this->userConnexion($client, 'admin');
        $crawler = $client->request('GET', '/tasks_undone');
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
        $crawler = $client->request('GET', '/tasks_undone');
        $form = $crawler->selectButton('Supprimer')->form();
        $client->submit($form);
        $client->followRedirect();
        $this->assertSelectorExists('.alert.alert-success'); 
    }
}