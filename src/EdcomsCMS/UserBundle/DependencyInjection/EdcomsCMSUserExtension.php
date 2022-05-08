<?php

namespace EdcomsCMS\UserBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class EdcomsCMSUserExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);



        $container->setParameter('edcoms.user.user_mapping', $config['mapping']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }

    /**
     * @param ContainerBuilder $container
     */
    public function prepend(ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');

        $this->prependFosUserConfig($bundles, $container);
        $this->prependSonataUserConfig($bundles, $container);
        $this->prependJMSSerializerConfig($bundles, $container);
    }

    /**
     * @param $bundles
     * @param ContainerBuilder $container
     * @throws \Exception
     */
    private function prependFosUserConfig($bundles, ContainerBuilder $container){
        if (!isset($bundles['FOSUserBundle'])) {
            throw new \Exception("FOSUserBundle bundle is not enabled");
        }

        $container->prependExtensionConfig('fos_user', array(
            'db_driver'  =>  'orm',
            'firewall_name'  =>  'admin',
            'user_class'  =>  'EdcomsCMS\UserBundle\Entity\User',
            'from_email' => array(
                'address'    => '%mailer_user%',
                'sender_name'   => '%mailer_user%'
            ),
            'group' => array(
                'group_class'    => 'EdcomsCMS\UserBundle\Entity\UserGroup'
            )
        ));
    }

    /**
     * @param $bundles
     * @param ContainerBuilder $container
     * @throws \Exception
     */
    private function prependSonataUserConfig($bundles, ContainerBuilder $container){
        if (!isset($bundles['SonataUserBundle'])) {
            throw new \Exception("SonataUserBundle bundle is not enabled");
        }

        $container->prependExtensionConfig('sonata_user', array(
            'manager_type'  =>  'orm',
            'class' => array(
                'user'    => 'EdcomsCMS\UserBundle\Entity\User',
                'group'   => 'EdcomsCMS\UserBundle\Entity\UserGroup'
            ),
            'admin'=> array(
                "user" => array(
                    "class" => 'EdcomsCMS\UserBundle\Admin\UserAdmin',
                    "controller" => 'edcoms.user.controller.user_controller'
                )
            )
        ));
    }

    /**
     * @param $bundles
     * @param ContainerBuilder $container
     * @throws \Exception
     */
    private function prependJMSSerializerConfig($bundles, ContainerBuilder $container){
        if (!isset($bundles['JMSSerializerBundle'])) {
            throw new \Exception("JMSSerializerBundle bundle is not enabled");
        }

        $container->prependExtensionConfig('jms_serializer', array(
            'metadata'  =>  array(
                'auto_detection' => true,
                'directories' => array(
                    'FOSUserBundle' => array(
                        'namespace_prefix' => 'FOS\\UserBundle',
                        'path' => '@EdcomsCMSUserBundle/Resources/config/serializer/FOSUserBundle'
                    )
                )
            )
        ));
    }
}
