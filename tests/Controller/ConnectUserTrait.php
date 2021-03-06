<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;

trait ConnectUserTrait
{
    private function userConnexion($client, $username)
    {
        $userRepository = static::$container->get(UserRepository::class);
        // retrieve the test user
        $testUser = $userRepository->findOneBy(['username' => $username]);
        // simulate $testUser being logged in
        return $client->loginUser($testUser);
    }
}
