<?php

namespace App\Entity;

use Symfony\Component\Security\Core\User\UserInterface;

interface MyUserInterface extends UserInterface
{
    public function getId(): ?int;
}