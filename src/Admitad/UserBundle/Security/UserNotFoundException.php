<?php

namespace Admitad\UserBundle\Security;

use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class UserNotFoundException extends UsernameNotFoundException
{

    protected $userData;

    public function getUserData()
    {
        return $this->userData;
    }

    public function setUserData($userData)
    {
        $this->userData = $userData;

        return $this;
    }
}
