<?php

namespace Admitad\UserBundle\Security\Firewall;

use Admitad\Api\Api;
use Admitad\UserBundle\Manager\Manager;
use Admitad\UserBundle\Security\Authentication\Token\OAuthToken;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Firewall\AbstractAuthenticationListener;

class OAuthListener extends AbstractAuthenticationListener
{
    /**
     * @var Manager
     */
    protected $manager;

    /**
     * @var Router
     */
    protected $router;

    public function getManager()
    {
        return $this->manager;
    }

    public function setManager(Manager $manager)
    {
        $this->manager = $manager;
    }

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
        if (!$request->query->has('code')) {
            return $this->redirectToAdmitad();
        }

        $api = new Api();
        $data = $api->requestAccessToken(
            $this->manager->getClientId(),
            $this->manager->getClientSecret(),
            $request->get('code'),
            $this->getRedirectUri()
        )->getResult();

        $token = new OAuthToken($data->getArrayCopy());
        return $this->authenticationManager->authenticate($token);
    }

    protected function redirectToAdmitad()
    {

        $api = new Api();
        return new RedirectResponse($api->getAuthorizeUrl(
            $this->manager->getClientId(),
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
