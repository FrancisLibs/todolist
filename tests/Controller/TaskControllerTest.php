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

class TaskControllerTest extends WebTestCase
{
    use FixturesTrait;
    use ConnectUserTrait;

    /**
    * @dataProvider provideUrls
    */
    public function testIndexIsRestricted($url)
    {
        $client = self::createClient();
        $this->loadFixtures([AppFixtures::class]);
        $client= $this->userConnexion($client, 'user'); 
        $client->request('GET', $url);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function provideUrls()
    {
        return [
            ['/tasks_undone'],
            ['/tasks_done']
        ];
    }
    
    public function testTaskCreateIsRedirect()
    {
        $client = self::createClient();
        $client->request('GET', '/tasks/create');
        // Si pas de connexion, redirection vers la page de login
        $this->assertResponseRedirects('/login', Response::HTTP_FOUND);
        $client->followRedirect();
        $this->assertSelectorExists('label', 'Nom d\'utilisateur :' ); 
    }

    public function testAccessCreateTaskPage()
    {
        $client = static::createClient();
        $this->loadFixtures([AppFixtures::class]);
        $client= $this->userConnexion($client, 'essai'); 
        $client->request('GET', '/tasks/create');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('button'); // Affichage du formulaire création de tâche
    }
    
    public function testUndoneTaskList()
    {
        $client = static::createClient();
        $this->loadFixtures([AppFixtures::class]);
        $client= $this->userConnexion($client, 'essai');
        $client->request('GET', '/tasks_undone');
        $this->assertResponseIsSuccessful();
    }

    public function testDoneTaskList()
    {
        $client = static::createClient();
        $this->loadFixtures([AppFixtures::class]);
        $client= $this->userConnexion($client, 'essai');
        $client->request('GET', '/tasks_done');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('button'); // Affichage du formulaire création de tâche
    }

    public function testTaskCreate()
    {
        $client = static::createClient();
        $this->loadFixtures([AppFixtures::class]);
        $client= $this->userConnexion($client, 'essai');
        $client->request('GET', '/tasks/create');
        // Remplissage formulaire
        $client->submitForm('Ajouter', [
            'task[title]'    => 'TestTitle',
            'task[content]' => 'Test content',
        ]);
        $client->followRedirect();
        $this->assertEquals('App\Controller\TaskController::listUndone', $client->getRequest()->attributes->get('_controller'));
       
        // Test insert in database
        $kernel = self::bootKernel();
        $em = $kernel->getContainer()->get('doctrine.orm.entity_manager');
        $query = $em->createQuery('SELECT count(t.id) from App\Entity\Task t WHERE t.title = :title AND t.content = :content');
        $query->setParameter('title', 'TestTitle');
        $query->setParameter('content', 'Test content');
        $this->assertTrue(0 < $query->getSingleScalarResult());
    }

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

    public function testToggleTask()
    {
        $client = static::createClient();
        $this->loadFixtures([AppFixtures::class]);
        $client= $this->userConnexion($client, 'admin');
        $client->request('GET', '/tasks/1/toggle');
        $client->followRedirect();
        $this->assertSelectorExists('.alert.alert-success');
    }

    public function testTaskDeleteByAdmin()
    {
        $client = static::createClient();
        $this->loadFixtures([AppFixtures::class]);
        $client= $this->userConnexion($client, 'admin');
        $taskRepository = static::$container->get(TaskRepository::class);
         /** @var Task $taskTest */
        $task = $taskRepository->find(16);
        $client->request('GET', '/tasks/' . $task->getId() . '/delete');
        $task = $taskRepository->find(16);
        $this->assertEquals(null, $task);
    }

    public function testTaskDeleteByOwner()
    {
        $client = static::createClient();
        $this->loadFixtures([AppFixtures::class]);
        $client= $this->userConnexion($client, 'essai');
        $taskRepository = static::$container->get(TaskRepository::class);
        /** @var Task $taskTest */
        $task = $taskRepository->find(16);
        $client->request('GET', '/tasks/' . $task->getId() . '/delete');
        $task = $taskRepository->find(16);
        $this->assertEquals(null, $task);
    }
}