<?php

namespace Admitad\UserBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class AdmitadUserExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {

        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $container->setParameter('admitad_user.config', $config);

        $manager = $container->getDefinition('admitad_user.manager');
        $manager
            ->addArgument($config['user_class'])
            ->addArgument($config['api'])
        ;
    }
}
