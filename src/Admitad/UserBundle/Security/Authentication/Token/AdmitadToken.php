<?php

namespace Admitad\UserBundle\Security\Authentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken as BaseAbstractToken;
use Symfony\Component\Security\Core\User\UserInterface;

abstract class AdmitadToken extends BaseAbstractToken
{
    protected $userData;

    public function __construct(array $userData, $roles = array())
    {
        parent::__construct($roles);

        $this->setAuthenticated(count($roles) > 0);

        $this->setAttribute('user_data', $userData);
    }

    public function getUserData($field = null)
    {
        $data = $this->getAttribute('user_data');
        return null === $field ? $data : (isset($data[$field]) ? $data[$field] : null);
    }

    public function getAccessToken()
    {
        return $this->getUserData('access_token');
    }

    public function getCredentials()
    {
        return '';
    }

    public function getRefreshToken()
    {
        return $this->getUserData('refresh_token');
    }

    public function getExpireIn()
    {
        return $this->getUserData('expire_in');
    }
}
