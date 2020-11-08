<?php

namespace App\Service;

use App\Entity\User; //l'entité user de notre aplication
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

class Securizer
{

    private $accesDecisionManager;

    public function __construct(AccessDecisionManagerInterface $accesDecisionManager)
    {
        $this->accesDecisionManager = $accesDecisionManager;
    }

    public function isGranted(User $user, $attribute, $object = null)
    {
        $token = new UsernamePasswordToken($user, 'none', 'none', $user->getRoles());
        return ($this->accesDecisionManager->decide($token, [$attribute], $object));
    }
}