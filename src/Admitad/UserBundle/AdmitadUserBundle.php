<?php

namespace Admitad\UserBundle;


use Admitad\UserBundle\DependencyInjection\Security\Factory\OAuthFactory;
use Admitad\UserBundle\DependencyInjection\Security\Factory\SignedRequestFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class AdmitadUserBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new OAuthFactory());
        $extension->addSecurityListenerFactory(new SignedRequestFactory());
    }
}
