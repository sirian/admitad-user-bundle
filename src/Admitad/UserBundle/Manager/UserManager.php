<?php

namespace Admitad\UserBundle\Manager;

use Admitad\Api\Api;
use Admitad\Api\Exception\Exception;
use Admitad\UserBundle\Api\ApiOptions;
use Admitad\UserBundle\Model\AdmitadUserInterface;
use Doctrine\Common\Persistence\ObjectManager;

class UserManager
{
    protected $apiOptions;
    protected $repository;
    protected $objectManager;
    protected $class;

    public function __construct(ApiOptions $apiOptions, ObjectManager $om, $class)
    {
        $this->apiOptions = $apiOptions;
        $this->objectManager = $om;
        $this->repository = $om->getRepository($class);

        $metadata = $om->getClassMetadata($class);
        $this->class = $metadata->getName();
    }

    public function refreshExpiredToken(AdmitadUserInterface $user)
    {
        if (!$user->isAdmitadTokenExpired()) {
            return true;
        }

        if (!$user->getAdmitadRefreshToken()) {
            return false;
        }
        
        try {
            $api = new Api($user->getAdmitadAccessToken());

            $data = $api
                ->refreshToken($this->apiOptions->getClientId(), $this->apiOptions->getClientSecret(), $user->getAdmitadRefreshToken())
                ->getArrayResult()
            ;

            $user->setAdmitadAccessToken($data['access_token']);
            $user->setAdmitadRefreshToken($data['refresh_token']);
            $user->setAdmitadTokenExpireIn($data['expires_in']);

            $this->updateUser($user);
        } catch (Exception $e) {
            $user->setAdmitadRefreshToken('');
            $user->setAdmitadAccessToken('');

            $this->updateUser($user);
            throw $e;
        }
        return true;
    }

    public function updateUser($user, $andFlush = true)
    {
        $this->objectManager->persist($user);
        if ($andFlush) {
            $this->objectManager->flush();
        }
    }

    public function createUser()
    {
        $class = $this->class;
        return new $class();
    }

    public function findOneBy($criteria)
    {
        return $this->repository->findOneBy($criteria);
    }
}
