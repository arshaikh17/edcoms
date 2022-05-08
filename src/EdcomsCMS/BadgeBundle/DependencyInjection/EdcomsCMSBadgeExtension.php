<?php

namespace EdcomsCMS\BadgeBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class EdcomsCMSBadgeExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }

    /**
     * @param ContainerBuilder $container
     */
    public function prepend(ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');

        $this->prependEdcomsCMSAdminConfig($bundles, $container);
    }

    /**
     * @param $bundles
     * @param ContainerBuilder $container
     * @throws \Exception
     */
    private function prependEdcomsCMSAdminConfig($bundles, ContainerBuilder $container){
        if (isset($bundles['EdcomsCMSAdminBundle'])) {
            $container->prependExtensionConfig('edcoms_cms_admin', array(
                'assets' => array(
                    'javascripts'   => [],
                    'stylesheets'   => []
                )
            ));
        }
    }
}
