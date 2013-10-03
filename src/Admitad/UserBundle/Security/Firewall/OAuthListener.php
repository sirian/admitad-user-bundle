<?php

namespace Admitad\UserBundle\Security\Firewall;

use Admitad\Api\Api;
use Admitad\Api\Exception\ApiException;
use Admitad\Api\Exception\Exception;
use Admitad\UserBundle\Security\Authentication\Token\OAuthToken;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class OAuthListener extends AbstractAuthenticationListener
{
    /**
     * @var Router
     */
    protected $router;

    public function getRouter()
    {
        return $this->router;
    }

    public function setRouter($router)
    {
        $this->router = $router;
    }

    protected function attemptAuthentication(Request $request)
    {
        if ($request->query->has('error')) {
            throw new AuthenticationException($request->query->get('error_description'));
        }

        if (!$request->query->has('code')) {
            return $this->redirectToAdmitad();
        }

        $api = new Api();
        try {
            $data = $api->requestAccessToken(
                $this->apiOptions->getClientId(),
                $this->apiOptions->getClientSecret(),
                $request->get('code'),
                $this->getRedirectUri()
            )->getResult();
        } catch (ApiException $e) {
            throw new AuthenticationException($e->getResponse()->getErrorDescription());
        }

        $token = new OAuthToken($data->getArrayCopy());
        return $this->authenticationManager->authenticate($token);
    }

    protected function redirectToAdmitad()
    {
        $api = new Api();
        return new RedirectResponse($api->getAuthorizeUrl(
            $this->apiOptions->getClientId(),
            $this->getRedirectUri(),
            $this->options['scope']
        ));
    }

    protected function getRedirectUri()
    {
        $redirectUri = $this->options['check_path'];
        if ($this->router->getRouteCollection()->get($redirectUri)) {
            $redirectUri = $this->router->generate($redirectUri, [], Router::ABSOLUTE_URL);
        }
        return $redirectUri;
    }
}
