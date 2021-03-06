<?php

namespace App\tests\Entity;

use App\Entity\Task;
use App\Entity\User;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserTest extends KernelTestCase
{
    private $user;
    private $task;

    public function setUp()
    {
        $this->user = (new User())
            ->setUsername('UtilisateurTest')
            ->setRoles(["ROLE_USER"])
            ->setPassword('password')
            ->setEmail('utilTest@gmail.com');
   
        $this->task = (new Task())
            ->setCreatedAt(new \DateTime('2011-01-01'))
            ->setTitle("TaskTitle")
            ->setContent("TaskContent");
    }

    public function assertHasErrors(User $user, int $number = 0)
    {
        self::bootKernel();
        $errors = self::$container->get('validator')->validate($user);
        $messages = [];
        /** @var constraintsViolation $errors */
        foreach ($errors as $error) {
            $messages[] = $error->getPropertyPath() . '=>' . $error->getMessage();
        }
        $this->assertCount($number, $errors, implode(', ', $messages));
    }

    public function testValidEntity()
    {
        $this->assertHasErrors($this->user, 0);
        $this->assertEquals("UtilisateurTest", $this->user->getUserName());
        $this->assertEquals(["ROLE_USER"], $this->user->getRoles());
        $this->assertEquals("password", $this->user->getPassword());
        $this->assertEquals("utilTest@gmail.com", $this->user->getEmail());
    }

    public function testAddAndRemoveTask()
    {
        $this->user->addTask($this->task);
        $this->assertEquals($this->user, $this->task->getUser());

        $tasksCollection = $this->user->getTasks();
        $this->assertEquals(1, count($tasksCollection));
        
        foreach ($tasksCollection as $task) {
            $this->user->removeTask($task);
        }
        $tasksCollection = $this->user->getTasks();
        $this->assertEquals(0, count($tasksCollection));
        $this->assertEquals(null, $this->task->getUser());
    }

    public function testMinLenghtUsername()
    {
        $this->assertHasErrors($this->user->setUsername("j"), 1);
    }
    public function testBlankUsername()
    {
        $this->assertHasErrors($this->user->setUsername(""), 1);
    }
    public function testMinLenghtPassword()
    {
        $this->assertHasErrors($this->user->setPassword("jkkkk"), 1);
    }
    public function testBlankPassword()
    {
        $this->assertHasErrors($this->user->setPassword(""), 1);
    }
    public function testBlankEmail()
    {
        $this->assertHasErrors($this->user->setEmail(""), 1);
    }
    public function testValidEmail()
    {
        $this->assertHasErrors($this->user->setEmail("d@f"), 1);
    }
    public function testEmailPattern()
    {
        $this->assertHasErrors($this->user->setEmail("d@f.com"), 0);
    }
}
