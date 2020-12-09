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
   
    public function __construct(UserPasswordEncoderInterface $passwordEncoder, Security $security)
    {
        $this->encoder = $passwordEncoder;
        $this->security = $security;
    }

    /**
     * @Route("/users", name="admin_user_list")
     * @param UserRepository $repository
     * @return response
     * @IsGranted("ROLE_ADMIN")
     */
    public function usersList(UserRepository $repository)
    {
        $users = $repository->findAll();
        return $this->render('user/list.html.twig', [
            'users' => $users
        ]);
    }

    /**
     * @Route("/users/create", name="user_create")
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return RedirectResponse|Response
     */
    public function userCreate(Request $request, EntityManagerInterface $manager)
    {       
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) 
        {          
            $user->setPassword($this->encoder->encodePassword($user, 'password'));
            $manager->persist($user);
            $manager->flush();

            $this->addFlash('success', "L'utilisateur a bien été ajouté.");
            if ($this->security->isGranted('ROLE_ADMIN')) {
                return $this->redirectToRoute('admin_user_list');
            }
            return $this->redirectToRoute('homepage');
        }
        return $this->render('user/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/users/{id}/edit", name="admin_user_edit")
     * @param User $user
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param Securizer $securizer
     * @return RedirectResponse|Response
     * @IsGranted("ROLE_ADMIN")
     */
    public function editAction(User $user, Request $request, EntityManagerInterface $manager, Securizer $securizer)
    {
        $form = $this->createForm(UserEditType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) 
        {
            $manager->flush();
            $this->addFlash('success', "L'utilisateur a bien été modifié");
            return $this->redirectToRoute('admin_user_list');
        }
        return $this->render('user/edit.html.twig', ['form' => $form->createView(), 'user' => $user]);
    }
}
