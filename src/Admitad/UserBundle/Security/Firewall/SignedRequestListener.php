<?php

namespace Admitad\UserBundle\Security\Firewall;

use Admitad\Api\Api;
use Admitad\Api\Exception\InvalidSignedRequestException;
use Admitad\UserBundle\Security\Authentication\Token\SignedRequestToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class SignedRequestListener extends AbstractAuthenticationListener
{
    protected function attemptAuthentication(Request $request)
    {
        $api = new Api();
        try {
            $userData = $api->parseSignedRequest($request->query->get('signed_request', ''), $this->apiOptions->getClientSecret());
        } catch (InvalidSignedRequestException $e) {
            throw new AuthenticationException('Invalid signed request', 0, $e);
        }

        $token = new SignedRequestToken($userData);

        return $this->authenticationManager->authenticate($token);
    }
}
