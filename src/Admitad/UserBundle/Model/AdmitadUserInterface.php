<?php

namespace Admitad\UserBundle\Model;

use Symfony\Component\Security\Core\User\UserInterface;

interface AdmitadUserInterface extends UserInterface
{
    public function getAdmitadId();
    public function setAdmitadId($id);

    public function getAdmitadAccessToken();
    public function setAdmitadAccessToken($token);

    public function getAdmitadRefreshToken();
    public function setAdmitadRefreshToken($token);

    public function setAdmitadTokenExpireIn($expires);
    public function isAdmitadTokenExpired();
}
