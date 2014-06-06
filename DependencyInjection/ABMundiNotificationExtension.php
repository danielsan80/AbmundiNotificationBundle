<?php

namespace ABMundi\NotificationBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class ABMundiNotificationExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        $container->setParameter('abm_notification.emails.from.email', $config['emails']['from']['email']);
        $container->setParameter('abm_notification.emails.from.name', $config['emails']['from']['name']);
        
        $container->setParameter('abm_notification.emails.debug.email', $config['emails']['debug']['email']);
        $container->setParameter('abm_notification.emails.debug.name', $config['emails']['debug']['name']);
        if ($container->getParameter('kernel.environment')=='dev') {
            $loader->load('services_dev.yml');
        }
        if ($container->getParameter('kernel.environment')=='test') {
            //$loader->load('services_test.yml');
        }
    }
    
}
