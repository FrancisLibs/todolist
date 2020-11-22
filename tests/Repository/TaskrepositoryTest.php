<?php

namespace App\Tests\Repository;

use App\Entity\Task;
use App\Entity\User;
use App\DataFixtures\AppFixtures;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Doctrine\Persistence\ObjectManager;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TaskRepositoryTest extends KernelTestCase
{
    use FixturesTrait;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

   /* public function testCount()
    {
        self::bootKernel();
        $this->loadFixtureFiles([__DIR__ . '/TaskRepositoryTestFixtures.yaml']);
        $tasks = self::$container->get(TaskRepository::class)->count([]);
        $this->assertEquals(3, $tasks);
    } */
    
    public function testFindAdminTasks()
    {
        self::bootKernel();
        $this->loadFixtures([AppFixtures::class]);

        $user = $this->entityManager
            ->getRepository(User::class)
            ->find(3);

        $taskRepository = $this->entityManager->getRepository(Task::class);
        $tasks = $taskRepository->findAdminTasks($user);

        $this->assertEquals(119, count($tasks));
    }

    public function testFindAdminDoneTasks()
    {
        self::bootKernel();
        $this->loadFixtures([AppFixtures::class]);

        $user = $this->entityManager
            ->getRepository(User::class)
            ->find(3);

        $taskRepository = $this->entityManager->getRepository(Task::class);
        $tasks = $taskRepository->findAdminDoneTasks($user);

        $this->assertEquals(121, count($tasks));
    }
}