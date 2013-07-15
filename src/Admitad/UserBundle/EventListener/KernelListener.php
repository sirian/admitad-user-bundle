<?php

namespace Admitad\UserBundle\EventListener;

use Admitad\Api\Exception\Exception;
use Admitad\UserBundle\Entity\User;
use Admitad\UserBundle\Manager\Manager;
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
     * @var Manager
     */
    protected $manager;

    /**
     * @var Router
     */
    protected $router;

    public function __construct(SecurityContext $securityContext, Manager $manager, Router $router)
    {
        $this->securityContext = $securityContext;
        $this->router = $router;
        $this->manager = $manager;
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

        try {
            $this->manager->refreshExpiredToken($user);
        } catch (Exception $e) {
            $authUrl = $this->router->generate('login_admitad_oauth');
            $event->setResponse(new RedirectResponse($authUrl));
        }
    }
}
