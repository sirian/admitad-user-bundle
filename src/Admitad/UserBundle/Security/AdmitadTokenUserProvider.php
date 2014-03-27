<?php

namespace Admitad\UserBundle\Security;

use Admitad\Api\Api;
use Admitad\Api\Exception\ApiException;
use Admitad\Api\Exception\Exception;
use Admitad\UserBundle\Api\ApiOptions;
use Admitad\UserBundle\Manager\UserManager;
use Admitad\UserBundle\Model\AdmitadUserInterface;
use Admitad\UserBundle\Security\Authentication\Token\AdmitadToken;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\AuthenticationServiceException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class AdmitadTokenUserProvider implements AdmitadTokenUserProviderInterface
{
    protected $apiOptions;
    protected $manager;

    public function __construct(UserManager $manager, ApiOptions $apiOptions)
    {
        $this->apiOptions = $apiOptions;
        $this->manager = $manager;
    }

    public function loadUserByToken(AdmitadToken $token)
    {
        $api = new Api($token->getAccessToken());
        try {
            $me = $api->me()->getResult();
        } catch (Exception $e) {
            throw new AuthenticationServiceException($e->getMessage());
        }

        $user = $this->loadUserByAdmitadData($me);

        if (!$user) {
            $exception = new UserNotFoundException();
            $exception
                ->setToken($token)
                ->setUserData($me)
            ;
            throw $exception;
        }

        $user->setAdmitadId($me['id']);
        $user->setAdmitadAccessToken($token->getAccessToken());
        $user->setAdmitadRefreshToken($token->getRefreshToken());
        $user->setAdmitadTokenExpireIn($token->getExpireIn());

        $this->manager->updateUser($user);

        return $user;
    }

    /**
     * @param $data
     * @return AdmitadUserInterface
     */
    public function loadUserByAdmitadData($data)
    {
        $user = $this->manager->findOneBy([
            'admitadId' => $data['id']
        ]);

        return $user;
    }
}
