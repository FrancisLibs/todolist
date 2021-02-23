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

    private function getUser($username)
    {
        return $user = self::$container
            ->get(UserRepository::class)
            ->findBy(['username' => $username]);
    }

    public function testFindAdminUndoneTasks()
    {
        self::bootKernel();
        $this->loadFixtures([AppFixtures::class]);
        $user = $this->getUser('admin');
        $tasks = self::$container
            ->get(TaskRepository::class)
            ->findAdminTasks($user, 0);
        $this->assertCount(16, $tasks);
    }

    public function testFindAdminDoneTasks()
    {
        self::bootKernel();
        $this->loadFixtures([AppFixtures::class]);
        $user = $this->getUser('admin');
        $tasks = self::$container
            ->get(TaskRepository::class)
            ->findAdminTasks($user, 1);
        $this->assertCount(14, $tasks);
    }
}