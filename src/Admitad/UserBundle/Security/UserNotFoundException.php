<?php

namespace Admitad\UserBundle\Security;

use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class UserNotFoundException extends UsernameNotFoundException
{
    protected $token;

    protected $userData;

    public function getToken()
    {
        return $this->token;
    }

    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

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
