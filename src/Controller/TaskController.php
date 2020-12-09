<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TaskController extends AbstractController
{
    /**
     * @Route("/tasks", name="task_list")
     * @param TaskRepository $taskRepository
     * @param UserRepository $userRepository
     * @return Response
     * @IsGranted("ROLE_USER")
     */
    public function index(TaskRepository $taskRepository, UserRepository $userRepository): Response
    {
        $user = $this->getUser();
        $anonymous = $userRepository->findOneBy(['username' => 'anonyme']);
        $hasAccess = $this->isGranted('ROLE_ADMIN');
        if(!$hasAccess)
        {
            $tasks = $taskRepository->findBy([
                'user' => $user,
                'isDone' => FALSE,
            ]);
        }
        else
        {
            $tasks = $taskRepository->findAdminTasks($user);
            foreach ($tasks as $task) {
                if ($task->getUser() == NULL) {
                    $task->setUser($anonymous);
                }
            }
        }
        return $this->render('task/list.html.twig', [
            'tasks' => $tasks,
            ]
        );
    }

    /**
     * @Route("/tasks_done", name="task_list_done")
     * @param TaskRepository $taskRepository
     * @param UserRepository $userRepository
     * @return Response
     * @IsGranted("ROLE_USER")
     */
    public function listDone(TaskRepository $taskRepository, UserRepository $userRepository): Response
    {
        $user = $this->getUser();
        $anonymous = $userRepository->findOneBy(['username' => 'anonyme']);
        $hasAccess = $this->isGranted('ROLE_ADMIN');
        if (!$hasAccess) {
            $tasks = $taskRepository->findBy([
                'user' => $user,
                'isDone' => TRUE,
            ]);
        } 
        else 
        {
            $tasks = $taskRepository->findAdminDoneTasks($user);
            foreach ($tasks as $task) {
                if ($task->getUser() == NULL) {
                    $task->setUser($anonymous);
                }
            }
        }
        return $this->render('task/list.html.twig', [
            'tasks' => $tasks,
        ]);
    }

    /**
     * @Route("/tasks/create", name="task_create")
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function taskCreate(Request $request, EntityManagerInterface $manager)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $task->setUser($user);
            $manager->persist($task);
            $manager->flush();
            $this->addFlash('success', 'La tâche a été bien été ajoutée.');
            return $this->redirectToRoute('task_list');
        }
        return $this->render('task/create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/tasks/{id}/edit", name="task_edit")
     * @param Task $task
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return RedirectResponse|Response
     * @Security ("is_granted('ROLE_ADMIN') or (is_granted('ROLE_USER') and user === task.getUser())")
     */
    public function taskEdit(Task $task, Request $request, EntityManagerInterface $manager)
    {
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) 
        {
            $manager->flush();

            $this->addFlash('success', 'La tâche a bien été modifiée.');

            return $this->redirectToRoute('task_list');
        }
        return $this->render('task/edit.html.twig', [
            'form' => $form->createView(),
            'task' => $task,
        ]);
    }

    /**
     * @Route("/tasks/{id}/toggle", name="task_toggle")
     * @param Task $task
     * @param EntityManagerInterface $manager
     * @return RedirectResponse
     * @Security ("is_granted('ROLE_ADMIN') or (is_granted('ROLE_USER') and user === task.getUser())")
     */
    public function toggleTask(Task $task, EntityManagerInterface $manager)
    {
        $task->toggle(!$task->isDone());
        $manager->flush();
        if ($task->isDone())
        {
            $this->addFlash('success', sprintf('La tâche %s a bien été marquée comme faite.', $task->getTitle()));
        }
        if (!$task->isDone()) 
        {
            $this->addFlash('success', sprintf('La tâche %s a bien été marquée comme non terminée.', $task->getTitle()));
        }
        return $this->redirectToRoute('task_list');
    }

    /**
     * @Route("/tasks/{id}/delete", name="task_delete")
     * @param Task $task
     * @param EntityManagerInterface $manager
     * @return RedirectResponse
     * @Security ("is_granted('ROLE_ADMIN') or (is_granted('ROLE_USER') and user === task.getUser())")
     */
    public function taskDelete(Task $task, EntityManagerInterface $manager)
    {
        $manager->remove($task);
        $manager->flush();
        $this->addFlash('success', 'La tâche a bien été supprimée.');
        return $this->redirectToRoute('task_list');
    }
}
