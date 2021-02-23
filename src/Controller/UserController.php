<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Form\UserEditType;
use App\Service\Securizer;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class UserController extends AbstractController
{
    private $encoder;
    private $security;
    private $manager;
   
    public function __construct(UserPasswordEncoderInterface $passwordEncoder, 
        Security $security, EntityManagerInterface $entityManager
    ) {
        $this->encoder = $passwordEncoder;
        $this->security = $security;
        $this->manager = $entityManager;
    }

    /**
     * Show user list if user is administrator
     * 
     * @Route("/users",         name="admin_user_list")
     * @param                   UserRepository $repository
     * @return                  response
     * @IsGranted("ROLE_ADMIN")
     */
    public function usersList(UserRepository $repository)
    {
        $users = $repository->findAll();
        return $this->render(
            'user/list.html.twig', [
            'users' => $users
            ]
        );
    }

    /**
     * Show create user form
     * 
     * @Route("/users/create", name="user_create")
     * @param                  Request                $request
     * @param                  EntityManagerInterface $manager
     * @return                 RedirectResponse|Response
     */
    public function userCreate(Request $request)
    {       
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {          
            $user->setPassword($this->encoder->encodePassword($user, 'password'));
            $this->manager->persist($user);
            $this->manager->flush();

            $this->addFlash('success', "L'utilisateur a bien été ajouté.");
            if ($this->security->isGranted('ROLE_ADMIN')) {
                return $this->redirectToRoute('admin_user_list');
            }
            return $this->redirectToRoute('homepage');
        }
        return $this->render(
            'user/create.html.twig', [
            'form' => $form->createView()
            ]
        );
    }

    /**
     * Show edit user form
     * 
     * @Route("/users/{id}/edit", name="admin_user_edit")
     * @param                     User                   $user
     * @param                     Request                $request
     * @param                     EntityManagerInterface $manager
     * @param                     Securizer              $securizer
     * @return                    RedirectResponse|Response
     * @IsGranted("ROLE_ADMIN")
     */
    public function userEdit(User $user, Request $request)
    {
        $form = $this->createForm(UserEditType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->flush();
            $this->addFlash('success', "L'utilisateur a bien été modifié");
            return $this->redirectToRoute('admin_user_list');
        }
        return $this->render(
            'user/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user
            ]
        );
    }

    /**
     * Delete user
     * 
     * @Route("/user/{id}/delete", name="user_delete", methods="DELETE")
     * @param                       User                   $user
     * @param                       EntityManagerInterface $manager
     * @return                      RedirectResponse
     * @IsGranted("ROLE_ADMIN")
     */
    public function userDelete(User $user, Request $request)
    {
        $submittedToken = $request->request->get('token');
        $currentUser = $this->getUser();
        if ($this->isCsrfTokenValid('delete-user', $submittedToken)) 
        {
            if($user <> $currentUser)
            {
                $this->manager->remove($user);
                $this->manager->flush();
                $this->addFlash('success', 'L\'utilisateur a bien été supprimé.');
                return $this->redirectToRoute('admin_user_list');
            }

            if($user == $currentUser)
            {
                $this->addFlash('error', 'Vous ne pouvez pas vous supprimer vous-même');
                return $this->redirectToRoute('admin_user_list');
            }
        }
        $this->addFlash('error', 'L\'utilisateur n\'a pas été supprimé.');
        return $this->redirectToRoute('admin_user_list');
    }
}
