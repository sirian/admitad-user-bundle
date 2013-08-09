<?php

namespace Admitad\UserBundle\DependencyInjection\Security\Factory;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\AbstractFactory;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;

class SignedRequestFactory extends AbstractFactory
{
    protected function createAuthProvider(ContainerBuilder $container, $id, $config, $userProviderId)
    {
        $providerId = 'admitad_user.authentication.provider.signed_request.' . $id;

        $container
            ->setDefinition($providerId, new DefinitionDecorator('admitad_user.authentication.provider'))
        ;

        return $providerId;
    }

    public function addConfiguration(NodeDefinition $node)
    {
        $this->addOption('require_previous_session', false);
        parent::addConfiguration($node);
    }

    protected function getListenerId()
    {
        return 'admitad_user.authentication.listener.signed_request';
    }

    public function getKey()
    {
        return 'admitad_signed_request';
    }

    public function getPosition()
    {
        return 'http';
    }
}
