<?php

namespace Admitad\UserBundle\Security\Authentication\Provider;

use Admitad\Api\Api;
use Admitad\UserBundle\Manager\Manager;
use Admitad\UserBundle\Security\Authentication\Token\AbstractToken;
use Admitad\UserBundle\Security\Authentication\Token\OAuthToken;
use Admitad\UserBundle\Security\Authentication\Token\SignedRequestToken;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserChecker;

class AdmitadProvider implements AuthenticationProviderInterface
{
    protected $userChecker;
    protected $manager;

    public function __construct(UserChecker $userChecker, Manager $manager)
    {
        $this->userChecker = $userChecker;
        $this->manager = $manager;
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof AbstractToken;
    }

    public function authenticate(TokenInterface $token)
    {
        echo 1;
        /**
         * @var AbstractToken $token
         */
        $user = $this->manager->loadUserByToken($token);

        if ($token instanceof SignedRequestToken) {
            $token = new SignedRequestToken($token->getUserData(), $user->getRoles());
        } elseif ($token instanceof OAuthToken) {
            $token = new OAuthToken($token->getUserData(), $user->getRoles());
        }

        $token->setUser($user);
        $token->setAuthenticated(true);
        $this->userChecker->checkPostAuth($user);

        return $token;
    }
}
