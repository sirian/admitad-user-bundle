<?php

namespace Admitad\UserBundle\Security\Authentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken as BaseAbstractToken;
use Symfony\Component\Security\Core\User\UserInterface;

abstract class AbstractToken extends BaseAbstractToken
{
    protected $accessToken;
    protected $refreshToken;
    protected $expireIn;

    protected $userData;

    public function __construct(array $userData, $roles = array())
    {
        parent::__construct($roles);

        $this->setAuthenticated(count($roles) > 0);

        $this->accessToken = $userData['access_token'];
        $this->refreshToken = $userData['refresh_token'];
        $this->expireIn = $userData['expires_in'];
        $this->userData = $userData;
    }

    public function getUserData()
    {
        return $this->userData;
    }

    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
    }

    public function getAccessToken()
    {
        return $this->accessToken;
    }

    public function getCredentials()
    {
        return '';
    }

    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    public function setRefreshToken($refreshToken)
    {
        $this->refreshToken = $refreshToken;
    }

    public function getExpireIn()
    {
        return $this->expireIn;
    }

    public function setExpireIn($expireIn)
    {
        $this->expireIn = $expireIn;
    }
}
