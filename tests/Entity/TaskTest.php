<?php

namespace App\tests\Entity;

use App\Entity\Task;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TaskTest extends KernelTestCase
{
    Public function getEntityTask()
    {
        return $task = (new Task())
            ->setCreatedAt(new \DateTime())
            ->setTitle("TestTask")
            ->setContent("TestContent")
            ->setUser($this->getEntityUser());
    }

    Public function getEntityUser()
    {
        return $user = (new User())
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
        $task = $this->getEntityTask();
        $this->assertHasErrors($task, 0);
        $this->assertEquals("TestTask", $task->getTitle());
        $this->assertEquals("TestContent", $task->getContent());
        $this->assertEquals($this->getEntityUser(), $task->getUser());
        $task->toggle(0);
        $this->assertEquals(0, $task->isDone());
        $task->toggle(1);
        $this->assertEquals(1, $task->isDone());
    }

    public function testInvalidBlankTaskTitle()
    {
        $this->assertHasErrors($this->getEntityTask()->setTitle(""), 1);
    }

    public function testInvalidBlankTaskContent()
    {
        $this->assertHasErrors($this->getEntityTask()->setContent(""), 1);
    }

}