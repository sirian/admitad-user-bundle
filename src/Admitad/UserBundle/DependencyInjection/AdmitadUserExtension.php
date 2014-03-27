<?php

namespace Admitad\UserBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
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

        $manager = $container->getDefinition('admitad_user.user_manager');
        $entityManagerName = $config['manager'] ? $config['manager'] . '_' : '';
        $manager
            ->addArgument(new Reference('doctrine.orm.' . $entityManagerName . 'entity_manager'))
            ->addArgument($config['user_class'])
        ;

        $apiConfig = $config['api'];
        $apiOptions = $container->getDefinition('admitad_user.api_options');
        $apiOptions
            ->replaceArgument(0, $apiConfig['client_id'])
            ->replaceArgument(1, $apiConfig['client_secret'])
        ;
    }
}
