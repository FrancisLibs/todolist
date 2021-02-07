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
    private $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }
    /**
     * Show task list to do
     * 
     * @Route("/tasks_undone", name="task_list_undone")
     * @param                  TaskRepository $taskRepository
     * @param                  UserRepository $userRepository
     * @return                 Response
     * 
     * @IsGranted("ROLE_USER")
     */
    public function ListUndone(TaskRepository $taskRepository, UserRepository $userRepository): Response
    {
        $user = $this->getUser();
        $anonymous = $userRepository->findOneBy(['username' => 'anonyme']);

        if($this->isGranted('ROLE_ADMIN'))
        {
            $tasks = $taskRepository->findAdminTasks($user);
            foreach ($tasks as $task) {
                if ($task->getUser() == NULL) {$task->setUser($anonymous);}
            }
            return $this->render('task/list.html.twig', [
                'tasks' => $tasks,
            ]);
        }

        $tasks = $taskRepository->findBy([
            'user' => $user,
            'isDone' => FALSE,
        ]);
        return $this->render('task/list.html.twig', [
            'tasks' => $tasks,
        ]);
    }

    /**
     * Show done task list 
     * 
     * @Route("/tasks_done",   name="task_list_done")
     * @param                  TaskRepository $taskRepository
     * @param                  UserRepository $userRepository
     * @return                 Response
     * @IsGranted("ROLE_USER")
     */
    public function listDone(TaskRepository $taskRepository, UserRepository $userRepository): Response
    {
        $user = $this->getUser();
        $anonymous = $userRepository->findOneBy(['username' => 'anonyme']);

        if ($this->isGranted('ROLE_ADMIN')) 
        {
            // If user has Administrator role, anonymous tasks are added
            $tasks = $taskRepository->findAdminDoneTasks($user);
            foreach ($tasks as $task) 
            {
                if ($task->getUser() == NULL) {$task->setUser($anonymous);}
            }
            return $this->render('task/list.html.twig', [
                'tasks' => $tasks,
            ]);
        } 

        $tasks = $taskRepository->findBy([
            'user' => $user,
            'isDone' => TRUE,
        ]);
        return $this->render('task/list.html.twig', [
            'tasks' => $tasks,
            ]
        );
    }

    /**
     * Show create task form
     * 
     * @Route("/tasks/create", name="task_create")
     * @param                  Request                $request
     * @param                  EntityManagerInterface $manager
     * @return                 Response
     *
     * @IsGranted("ROLE_USER")
     */
    public function taskCreate(Request $request)
    {
        $user = $this->getUser();
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $task->setUser($user);
            $this->manager->persist($task);
            $this->manager->flush();
            $this->addFlash('success', 'La tâche a été bien été ajoutée.');
            return $this->redirectToRoute('task_list_undone');
        }
        return $this->render('task/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * Show edit task form
     * 
     * @Route("/tasks/{id}/edit", name="task_edit")
     * @param                     Task                   $task
     * @param                     Request                $request
     * @param                     EntityManagerInterface $manager
     * @return                    RedirectResponse|Response
     * @Security ("is_granted('ROLE_ADMIN') or (is_granted('ROLE_USER') and user === task.getUser())")
     */
    public function taskEdit(Task $task, Request $request)
    {
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->flush();

            $this->addFlash('success', 'La tâche a bien été modifiée.');

            return $this->redirectToRoute('task_list_undone');
        }
        return $this->render(
            'task/edit.html.twig', [
            'form' => $form->createView(),
            'task' => $task,
            ]
        );
    }

    /**
     * Toggle task action
     * 
     * @Route("/tasks/{id}/toggle", name="task_toggle")
     * @param                       Task                   $task
     * @param                       EntityManagerInterface $manager
     * @return                      RedirectResponse
     * @Security ("is_granted('ROLE_USER') and user === task.getUser()")
     */
    public function toggleTask(Task $task, Request $request)
    {
        $submittedToken = $request->request->get('token');
        
        if ($this->isCsrfTokenValid('toggle-item', $submittedToken)) 
        {
            $task->toggle(!$task->isDone());
            $this->manager->flush();
            if ($task->isDone()) {
                $this->addFlash('success', sprintf('La tâche %s a bien été marquée comme faite.', $task->getTitle()));
            }
            if (!$task->isDone()) {
                $this->addFlash('success', 
                    sprintf('La tâche %s a bien été marquée comme non terminée.', 
                        $task->getTitle()
                    )
                );
            }
            return $this->redirectToRoute('task_list_undone');
        }

        $this->addFlash('error', 'La tâche n\'a pas été modifiée.');
        return $this->redirectToRoute('task_list_undone');
    }

    /**
     * Delete task action
     * 
     * @Route("/tasks/{id}/delete", name="task_delete")
     * @param                       Task                   $task
     * @param                       EntityManagerInterface $manager
     * @return                      RedirectResponse
     * @Security                    ("is_granted('ROLE_ADMIN') or (is_granted('ROLE_USER') and user === task.getUser())")
     */
    public function taskDelete(Task $task, Request $request)
    {
        $submittedToken = $request->request->get('token');

        if ($this->isCsrfTokenValid('delete-item', $submittedToken)) 
        {
            $this->manager->remove($task);
            $this->manager->flush();
            $this->addFlash('success', 'La tâche a bien été supprimée.');
            return $this->redirectToRoute('task_list_undone');
        }
        $this->addFlash('error', 'La tâche n\'a pas été supprimée.');
        return $this->redirectToRoute('task_list_undone');
    }
}
