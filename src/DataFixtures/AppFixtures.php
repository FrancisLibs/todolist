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
        $nbUser = 20;
        $users = [];

        //USERS
        for($i=0;$i<$nbUser;$i++){
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
            }

            $users[] = $user;
            $manager->persist($user);
        }
        // TASK WITHOUT USER
        for($i=0;$i<25;$i++)
        {
            $task  = new Task();
            $task->setTitle($faker->sentence());
            $task->setContent($faker->text(250));
            $task->setCreatedAt($faker->dateTimeBetween('-2 years'));
            $task->isDone();
            $task->toggle(boolval(rand(0,1)));
            $manager->persist($task);
        }

        //TASK WITH USER
        for($i=0;$i<150;$i++)
        {
            $task  = new Task();
            $task->setUser($users[rand(0,$nbUser-1)]);
            $task->setTitle($faker->sentence());
            $task->setContent($faker->text(250));
            $task->setCreatedAt($faker->dateTimeBetween('-100 days'));
            $task->isDone();
            $task->toggle(boolval(rand(0,1)));

            $manager->persist($task);
        }
        $manager->flush();
    }
}
