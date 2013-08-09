<?php

namespace Admitad\UserBundle\Security\Firewall;

use Admitad\Api\Exception\InvalidSignedRequestException;
use Admitad\UserBundle\Manager\Manager;
use Admitad\UserBundle\Security\Authentication\Token\SignedRequestToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Firewall\AbstractAuthenticationListener;

class SignedRequestListener extends AbstractAuthenticationListener
{
    /**
     * @var Manager
     */
    protected $manager;

    public function getManager()
    {
        return $this->manager;
    }

    public function setManager(Manager $manager)
    {
        $this->manager = $manager;
    }

    protected function attemptAuthentication(Request $request)
    {
        $api = $this->manager->getAdmitadApi();
        try {
            $userData = $api->parseSignedRequest($request->query->get('signed_request', ''), $this->manager->getClientSecret());
        } catch (InvalidSignedRequestException $e) {
            throw new AuthenticationException('Invalid signed request', 0, $e);
        }

        $token = new SignedRequestToken($userData);

        return $this->authenticationManager->authenticate($token);
    }
}
