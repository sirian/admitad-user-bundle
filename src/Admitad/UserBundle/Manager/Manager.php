<?php

namespace Admitad\UserBundle\Manager;

use Admitad\Api\Api;
use Admitad\Api\Exception\Exception;
use Admitad\UserBundle\Entity\User;
use FOS\UserBundle\Model\UserManagerInterface;

class Manager
{
    private $clientId;
    private $clientSecret;

    /**
     * @var UserManagerInterface
     */
    private $userManager;

    public function __construct(UserManagerInterface $userManager, $clientId, $clientSecret)
    {
        $this->userManager = $userManager;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
    }

    public function refreshExpiredToken(User $user)
    {
        if ($user->isAdmitadTokenExpired() && $user->getAdmitadRefreshToken()) {
            try {
                $api = $this->getAdmitadApi($user);
                $data = $api
                    ->refreshToken($this->clientId, $this->clientSecret, $user->getAdmitadRefreshToken())
                    ->getArrayResult()
                ;
                $user->setAdmitadAccessToken($data['access_token']);
                $user->setAdmitadRefreshToken($data['refresh_token']);
                $user->setAdmitadTokenExpireIn($data['expires_in']);
                $this->userManager->updateUser($user);
            } catch (Exception $e) {
                $user->setAdmitadRefreshToken('');
                $this->userManager->updateUser($user);
                throw $e;
            }
        }
    }

    public function getClientId()
    {
        return $this->clientId;
    }

    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    public function getAdmitadApi(User $user)
    {
        return new Api($user->getAdmitadAccessToken());
    }
}
