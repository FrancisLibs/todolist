<?php

namespace App\Tests\Repository;

use App\DataFixtures\AppFixtures;
use App\Repository\UserRepository;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserRepositoryTest extends KernelTestCase
{
    use FixturesTrait;
    
    public function testCount()
    {
        self::bootKernel();
        $this->loadFixtures([AppFixtures::class]);
        $users = self::$container->get(UserRepository::class)->count([]);
        $this->assertEquals(15, $users);
    }
}
