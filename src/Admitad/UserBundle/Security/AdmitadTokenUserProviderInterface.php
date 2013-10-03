<?php

namespace Admitad\UserBundle\Security;

use Admitad\UserBundle\Security\Authentication\Token\AdmitadToken;
use Symfony\Component\Security\Core\User\UserInterface;

interface AdmitadTokenUserProviderInterface
{
    /**
     * @param AdmitadToken $token
     * @return UserInterface
     */
    public function loadUserByToken(AdmitadToken $token);
}
