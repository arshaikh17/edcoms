<?php

namespace EdcomsCMS\MaintenanceBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class EdcomsCMSMaintenanceExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        foreach ($config as $key => $value) {
            $container->setParameter('edcoms.maintenance.'.$key, $value);
        }

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }

    /**
     * @param ContainerBuilder $container
     */
    public function prepend(ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $container->getExtensionConfig('edcoms_cms_maintenance'));
        $this->prependSettingsConfig($bundles, $container, $config);
    }

    /**
     * @param $bundles
     * @param ContainerBuilder $container
     * @throws \Exception
     */
    private function prependSettingsConfig($bundles, ContainerBuilder $container, $config){
        if (!isset($bundles['EdcomsCMSSettingsBundle'])) {
            throw new \Exception("EdcomsCMSSettingsBundle is not enabled");
        }

        $container->prependExtensionConfig('edcoms_cms_settings',[
            'categories' => ['Maintenance'],
            'settings' => [
                'maintenance' => [
                    'category' => 'Maintenance',
                    'type'     => 'choice',
                    'options' => [
                        'required' => false,
                        'choices' => ['off', 'on']
                    ]
                ]
            ]
        ]);
    }
}
