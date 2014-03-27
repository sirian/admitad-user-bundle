<?php

namespace Admitad\UserBundle\DependencyInjection\Security\Factory;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\AbstractFactory;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;

class OAuthFactory extends AbstractFactory
{
    protected function createAuthProvider(ContainerBuilder $container, $id, $config, $userProviderId)
    {
        $providerId = 'admitad_user.authentication.provider.oauth.' . $id;

        $container
            ->setDefinition($providerId, new DefinitionDecorator('admitad_user.authentication.provider'))
        ;

        return $providerId;
    }

    public function addConfiguration(NodeDefinition $node)
    {
        $this->addOption('scope', 'private_data private_data_email');
        parent::addConfiguration($node);
    }


    protected function getListenerId()
    {
        return 'admitad_user.authentication.listener.oauth';
    }

    public function getKey()
    {
        return 'admitad_oauth';
    }

    public function getPosition()
    {
        return 'http';
    }
}
