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

class TaskCreateControllerTest extends WebTestCase
{
    use FixturesTrait;
    use ConnectUserTrait;
    
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

    public function testTaskCreate()
    {
        $client = static::createClient();
        //$this->loadFixtures([AppFixtures::class]);
        $client= $this->userConnexion($client, 'essai');
        $client->request('GET', '/tasks/create');
        // Remplissage formulaire
        $client->submitForm('Ajouter', [
            'task[title]'    => 'TestTitle',
            'task[content]' => 'Test content',
        ]);
        $client->followRedirect();
        $this->assertEquals('App\Controller\TaskController::tasksList', $client->getRequest()->attributes->get('_controller'));
        // Test insert in database
        $kernel = self::bootKernel();
        $manager = $kernel->getContainer()->get('doctrine.orm.entity_manager');
        $query = $manager->createQuery('SELECT count(t.id) from App\Entity\Task t WHERE t.title = :title AND t.content = :content');
        $query->setParameter('title', 'TestTitle');
        $query->setParameter('content', 'Test content');
        $this->assertTrue(0 < $query->getSingleScalarResult());
    }
}