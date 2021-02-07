<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Task;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->encoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();

        for($j=0;$j<15;$j++)    // Tasks without users
        {
            $task  = new Task();
            $task->setTitle($faker->sentence());
            $task->setContent($faker->text(250));
            $task->setCreatedAt($faker->dateTimeBetween('-2 years'));
            $task->isDone();
            if($j > 7)
            {
                $task->toggle(1);
            }
            $manager->persist($task);
        }

        //USERS
        for($i=0;$i<15;$i++){       // Creation of 15 users
            $user  = new User();
            $user->setEmail($faker->email);
            $user->setPassword($this->encoder->encodePassword($user,'password'));
            $user->setUsername($faker->userName);

            switch ($i) 
            {
                case 0:
                    $user->setUsername('essai');
                    break;
                case 1:
                    $user->setUsername('anonyme');
                    break;
                case 2:
                    $user->setUsername('admin');
                    $user->setRoles(['ROLE_ADMIN']);
                    break;
                case 3:
                    $user->setUsername('user');
                    $user->setRoles(['ROLE_USER']);
                    break;
            }
            $manager->persist($user);

            for($j=0;$j<15;$j++)    //Tasks with users
            {
                $task  = new Task();
                $task->setTitle($faker->sentence());
                $task->setContent($faker->text(250));
                $task->setCreatedAt($faker->dateTimeBetween('-100 days'));
                $task->setUser($user);
                $task->isDone();
                if($j > 7)
                {
                    $task->toggle(1);
                }
                $manager->persist($task);
            }
        }

        $manager->flush();
    }
}
