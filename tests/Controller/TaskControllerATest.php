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

class TaskControllerATest extends WebTestCase
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
        $this->assertSelectorTextContains('.btn.btn-success', 'Marquer comme faite');
    }

    public function testDoneTaskList()
    {
        $client = static::createClient();
        $this->loadFixtures([AppFixtures::class]);
        $client= $this->userConnexion($client, 'essai');
        $client->request('GET', '/tasks_done');
        $this->assertSelectorExists('.btn.btn-success', 'Marquer non terminée'); // Affichage du bouton du formulaire de création de tâche
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
}