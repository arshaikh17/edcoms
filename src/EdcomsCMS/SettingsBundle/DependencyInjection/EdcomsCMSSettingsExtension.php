<?php

namespace EdcomsCMS\SettingsBundle\DependencyInjection;

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
class EdcomsCMSSettingsExtension extends Extension implements PrependExtensionInterface
{

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        foreach ($config as $key => $value) {
            $container->setParameter('edcoms.settings_manager.'.$key, $value);
        }

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        $loader->load('admins.yml');
    }

    /**
     * @param ContainerBuilder $container
     */
    public function prepend(ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $container->getExtensionConfig('edcoms_cms_settings'));
        $this->prependDmishhSettingsConfig($bundles, $container, $config);
    }

    /**
     * @param $bundles
     * @param ContainerBuilder $container
     * @throws \Exception
     */
    private function prependDmishhSettingsConfig($bundles, ContainerBuilder $container, $config){
        if (!isset($bundles['DmishhSettingsBundle']) && array_key_exists('settings', $config)) {
            throw new \Exception("DmishhSettingsBundle bundle is not enabled");
        }
        //update the configurations with only the data needed for Dmishh Bundle
        $settings=[];
        foreach ($config['settings'] as $index => $value) {
            unset($value['category']);//remove category option as it does not exists in the Dmishh bundle
            $settings[$index] = $value;
        }
        $container->prependExtensionConfig('dmishh_settings', [
            'serialization'=>$config['serialization'],
            'settings'=>$settings,
        ]);
    }
}
