<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DefaultController extends AbstractController
{
    /**
     * Homepage
     * 
     * @Route("/", name="homepage")
     * @return     Response
     */
    public function index()
    {   
        return $this->render('default/index.html.twig');
    }
}
