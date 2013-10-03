<?php

namespace Admitad\UserBundle\EventListener;

use Admitad\Api\Exception\Exception;
use Admitad\UserBundle\Manager\UserManager;
use Admitad\UserBundle\Model\AdmitadUserInterface;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
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
     * @var UserManager
     */
    protected $manager;

    /**
     * @var Router
     */
    protected $router;

    public function __construct(SecurityContext $securityContext, UserManager $manager, Router $router)
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

        $user = $token->getUser();


        if (!$user instanceof AdmitadUserInterface) {
            return;
        }

        try {
            if ($user->isAdmitadTokenExpired()) {
                $this->manager->refreshExpiredToken($user);
            }
        } catch (Exception $e) {
            //todo: probably logout user
        }
    }
}
