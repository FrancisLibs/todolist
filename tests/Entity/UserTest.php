<?php

namespace App\tests\Entity;

use App\Entity\Task;
use App\Entity\User;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserTest extends KernelTestCase
{
    Public function getEntityTask()
    {
        return $task = (new Task())
            ->setCreatedAt(new \DateTime('2011-01-01'))
            ->setTitle("TaskTitle")
            ->setContent("TaskContent");
    }
   
    Public function getEntityUser()
    {
        return $user = (new User())
            ->setUsername('UtilisateurTest')
            ->setRoles(["ROLE_USER"])
            ->setPassword('password')
            ->setEmail('utilTest@gmail.com')
            ->addTask($this->getEntityTask());
    }

    public function assertHasErrors(User $user, int $number = 0)
    {
        self::bootKernel();
        $errors = self::$container->get('validator')->validate($user);

        $messages = [];

        /** @var constraintsViolation $errors */
        foreach ($errors as $error)
        {
            $messages[] = $error->getPropertyPath() . '=>' . $error->getMessage();
        }
        $this->assertCount($number, $errors, implode(', ', $messages));
    }

    public function testValidEntity()
    {
        $user = $this->getEntityUser();
        $this->assertHasErrors($user, 0);
        $this->assertEquals("UtilisateurTest", $user->getUserName());
        $this->assertEquals(["ROLE_USER"], $user->getRoles());
        $this->assertEquals("password", $user->getPassword());
        $this->assertEquals("utilTest@gmail.com", $user->getEmail());
        $taskCollection = $user->getTasks();
        $date = new \DateTime('2011-01-01');
        $this->assertEquals($date, $taskCollection->last()->getCreatedAt());
    }

    public function testTaskUser()
    {
        $user = $this->getEntityUser();
        $task = $this->getEntityTask();

        $tasksCollection = $user->getTasks();
        foreach($tasksCollection as $task)
        {
            $user->removeTask($task);
        }

        $tasksCollection = $user->getTasks();
        $this->assertEquals(true, $tasksCollection->isEmpty());
        
        $user->addTask($task);
        $tasksCollection = $user->getTasks();
        $this->assertNotEmpty($tasksCollection);
    }

    public function testMinLenghtUsername()
    {
        $this->assertHasErrors($this->getEntityUser()->setUsername("j"), 1);
    }

    public function testBlankUsername()
    {
        $this->assertHasErrors($this->getEntityUser()->setUsername(""), 1);
    }

    public function testMinLenghtPassword()
    {
        $this->assertHasErrors($this->getEntityUser()->setPassword("jkkkk"), 1);
    }

    public function testBlankPassword()
    {
        $this->assertHasErrors($this->getEntityUser()->setPassword(""), 1);
    }


    public function testBlankEmail()
    {
        $this->assertHasErrors($this->getEntityUser()->setEmail(""), 1);
    }

    public function testValidEmail()
    {
        $this->assertHasErrors($this->getEntityUser()->setPassword("d@f"), 1);
    }

    public function testEmailPattern()
    {
        $this->assertHasErrors($this->getEntityUser()->setPassword("d@f.com"), 0);
    }
}