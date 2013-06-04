<?php

namespace Admitad\UserBundle\EventListener;

use Admitad\Api\Api;
use Admitad\Api\ApiException;
use Admitad\UserBundle\Entity\User;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\Security\Core\SecurityContext;

class KernelListener
{
    /**
     * @var SecurityContext
     */
    protected $securityContext;

    /**
     * @var UserManagerInterface
     */
    protected $userManager;

    /**
     * @var Router
     */
    protected $router;

    protected $clientId;
    protected $clientSecret;

    public function __construct(SecurityContext $securityContext, UserManagerInterface $userManager, Router $router, $clientId, $clientSecret)
    {
        $this->securityContext = $securityContext;
        $this->userManager = $userManager;
        $this->router = $router;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (HttpKernel::MASTER_REQUEST != $event->getRequestType()) {
            return;
        }

        $this->checkAdmitadTokenExpired($event);
    }

    protected function checkAdmitadTokenExpired(GetResponseEvent $event)
    {
        $token = $this->securityContext->getToken();
        if (null === $token) {
            return;
        }

        /**
         * @var User $user
         */
        $user = $token->getUser();


        if (!$user instanceof User) {
            return;
        }

        if ($user->isAdmitadTokenExpired() && $user->getAdmitadRefreshToken()) {
            try {
                $api = new Api();
                $data = $api
                    ->refreshToken($this->clientId, $this->clientSecret, $user->getAdmitadRefreshToken())
                    ->getArrayResult()
                ;
                $user->setAdmitadAccessToken($data['access_token']);
                $user->setAdmitadRefreshToken($data['refresh_token']);
                $user->setAdmitadTokenExpireIn($data['expires_in']);
                $this->userManager->updateUser($user);
            } catch (ApiException $e) {
                $authUrl = $this->router->generate('login_admitad_oauth');
                $user->setAdmitadRefreshToken('');
                $this->userManager->updateUser($user);
                $event->setResponse(new RedirectResponse($authUrl));
            }
        }
    }
}
