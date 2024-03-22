<?php

namespace App\Security;

use Symfony\Component\Security\Core\User\UserInterface;

class SystemUser implements UserInterface
{
    public function getRoles(): array
    {
        return ["ROLE_SYSTEM"];
    }

    public function eraseCredentials()
    {
    }

    public function getUserIdentifier(): string
    {
        return "SYSTEM";
    }
}