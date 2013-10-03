<?php

namespace Admitad\UserBundle\Security\Authentication\Provider;

use Admitad\UserBundle\Security\AdmitadTokenUserProviderInterface;
use Admitad\UserBundle\Security\Authentication\Token\AdmitadToken;
use Admitad\UserBundle\Security\Authentication\Token\OAuthToken;
use Admitad\UserBundle\Security\Authentication\Token\SignedRequestToken;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationServiceException;
use Symfony\Component\Security\Core\User\UserChecker;
use Symfony\Component\Security\Core\User\UserInterface;

class AdmitadProvider implements AuthenticationProviderInterface
{
    protected $userChecker;
    protected $provider;

    public function __construct(UserChecker $userChecker, AdmitadTokenUserProviderInterface $provider)
    {
        $this->userChecker = $userChecker;
        $this->provider = $provider;
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof AdmitadToken;
    }

    public function authenticate(TokenInterface $token)
    {
        /**
         * @var AdmitadToken $token
         */
        $user = $this->provider->loadUserByToken($token);

        if (!$user instanceof UserInterface) {
            throw new AuthenticationServiceException('The user provider must return a UserInterface object.');
        }

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
