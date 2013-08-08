<?php

namespace Admitad\UserBundle\Manager;

use Admitad\Api\Api;
use Admitad\Api\Exception\Exception;
use Admitad\UserBundle\Model\UserInterface;
use Admitad\UserBundle\Security\Authentication\Token\AbstractToken;
use Doctrine\Bundle\DoctrineBundle\Registry;

class Manager
{
    protected $userEntityManager;
    protected $userClass;
    protected $apiOptions;

    public function __construct(Registry $doctrine, $userClass, $apiOptions)
    {
        $this->userEntityManager = $doctrine->getManagerForClass($userClass);
        $this->apiOptions = $apiOptions;
        $this->userClass = $userClass;
    }

    public function refreshExpiredToken(UserInterface $user)
    {
        if ($user->isAdmitadTokenExpired()) {
            if (!$user->getAdmitadRefreshToken()) {
                return false;
            }
            try {
                $api = $this->getAdmitadApi($user);
                $data = $api
                    ->refreshToken($this->getClientId(), $this->getClientSecret(), $user->getAdmitadRefreshToken())
                    ->getArrayResult()
                ;
                $user->setAdmitadAccessToken($data['access_token']);
                $user->setAdmitadRefreshToken($data['refresh_token']);
                $user->setAdmitadTokenExpireIn($data['expires_in']);
                $this->userEntityManager->flush($user);
            } catch (Exception $e) {
                $user->setAdmitadRefreshToken('');
                $user->setAdmitadAccessToken('');
                $this->userEntityManager->flush($user);
                throw $e;
            }
        }
        return true;
    }

    public function getClientId()
    {
        return $this->apiOptions['client_id'];
    }

    public function getClientSecret()
    {
        return $this->apiOptions['client_secret'];
    }

    public function getAdmitadApi(UserInterface $user = null)
    {
        return new Api($user ? $user->getAdmitadAccessToken() : null);
    }

    public function loadUserByToken(AbstractToken $token)
    {
        $api = new Api($token->getAccessToken());

        $me = $api->me()->getResult();

        $user = $this->userEntityManager->getRepository($this->userClass)->findOneBy(['admitadId' => $me['id']]);

        if (!$user) {
            $user = $this->createUser();
        }

        if (isset($me['email'])) {
            $user->setEmail($me['email']);
        }

        $user->setAdmitadId($me['id']);
        $user->setUsername($me['username']);
        $user->setFirstName($me['first_name']);
        $user->setLastName($me['last_name']);
        $user->setAdmitadAccessToken($token->getAccessToken());
        $user->setAdmitadRefreshToken($token->getRefreshToken());
        $user->setAdmitadTokenExpireIn($token->getExpireIn());

        $this->userEntityManager->flush($user);

        return $user;
    }

    /**
     * @return UserInterface
     */
    public function createUser()
    {
        $class = $this->userClass;
        return new $class();
    }
}
