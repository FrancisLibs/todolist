<?php

namespace App\tests\Entity;

use App\Entity\Task;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TaskTest extends KernelTestCase
{
    private $task;
    private $user;

    Public function setUp()
    {
        $this->task = (new Task())
            ->setCreatedAt(new \DateTime())
            ->setTitle("TestTask")
            ->setContent("TestContent");

        $this->user = (new User())
            ->setUsername('UtilisateurTest')
            ->setRoles(["ROLE_USER"])
            ->setPassword('password')
            ->setEmail('utilTest@gmail.com');
    }

    public function assertHasErrors(Task $task, int $number = 0)
    {
        self::bootKernel();
        $errors = self::$container->get('validator')->validate($task);
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
        $this->assertHasErrors($this->task, 0);
        $this->assertEquals("TestTask", $this->task->getTitle());
        $this->assertEquals("TestContent", $this->task->getContent());
        $this->task->setUser($this->user);
        $this->assertEquals($this->user, $this->task->getUser());
        $this->task->toggle(0);
        $this->assertEquals(0, $this->task->isDone());
        $this->task->toggle(1);
        $this->assertEquals(1, $this->task->isDone());
    }

    public function testInvalidBlankTaskTitle()
    {
        $this->assertHasErrors($this->task->setTitle(""), 1);
    }

    public function testInvalidBlankTaskContent()
    {
        $this->assertHasErrors($this->task->setContent(""), 1);
    }

}