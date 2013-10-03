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
            throw new UsernameNotFoundException();
        }

        $user->setAdmitadId($me['id']);
        $user->setAdmitadAccessToken($token->getAccessToken());
        $user->setAdmitadRefreshToken($token->getRefreshToken());
        $user->setAdmitadTokenExpireIn($token->getExpireIn());

        $propertyAccessor = new PropertyAccessor();
        foreach ($this->apiOptions->getPaths() as $property => $path) {
            if (!isset($me[$path])) {
                continue;
            }
            $propertyAccessor->setValue($user, $property, $me[$path]);
        }

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

        if (!$user) {
            $user = $this->manager->createUser();
        }

        return $user;
    }
}
