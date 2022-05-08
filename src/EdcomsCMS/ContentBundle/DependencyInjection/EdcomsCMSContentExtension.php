<?php

namespace EdcomsCMS\ContentBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class EdcomsCMSContentExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        // Once the services definition are read, get your service and add a method call to setConfig()
        $container->setParameter('edcoms_cms_content.email', $config['email']);
        $container->setParameter('edcoms_cms_content.configuration', $config);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $loader->load('sonata/services.yml');
        $loader->load('sonata/admins.yml');
        $loader->load('sonata/form_types.yml');
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
                    'javascripts'   => [
                        '/bundles/pixsortablebehavior/js/init.js',
                        '/bundles/edcomscmscontent/js/main.js'
                    ],
                    'stylesheets'   => [
                        "bundles/edcomscmscontent/css/main.css"
                    ]
                )
            ));
        }
    }


}
