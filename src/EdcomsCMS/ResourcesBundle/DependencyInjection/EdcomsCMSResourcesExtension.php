<?php

namespace EdcomsCMS\ResourcesBundle\DependencyInjection;

use EdcomsCMS\ResourcesBundle\Entity\Context\ResourceSubjectContext;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class EdcomsCMSResourcesExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $container->setParameter('edcoms_cms_resources.configuration',$config);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('admins.yml');
        $loader->load('services.yml');
    }

    /**
     * @param ContainerBuilder $container
     */
    public function prepend(ContainerBuilder $container)
    {

        $bundles = $container->getParameter('kernel.bundles');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $container->getExtensionConfig('edcoms_cms_resources'));

        $this->prependEdcomsContentConfig($bundles, $container, $config);

        $this->prependORMConfig($bundles, $container, $config);
    }

    /**
     * @param $bundles
     * @param ContainerBuilder $container
     * @throws \Exception
     */
    private function prependORMConfig($bundles, ContainerBuilder $container, $config){


        if (isset($bundles['DoctrineBundle'])) {
            $container->prependExtensionConfig('doctrine', array(
                'orm' => array(
                    'resolve_target_entities' => array(
                        'EdcomsCMS\ResourcesBundle\Model\ResourceSubjectInterface' => $config['resource_subject']['entity_class'],
                        'EdcomsCMS\ResourcesBundle\Model\ResourceInterface' => $config['base_resource'],
                        'EdcomsCMS\ResourcesBundle\Model\VideoResourceInterface' => $config['video_resource']['entity_class'],
                        'EdcomsCMS\ResourcesBundle\Model\ResourceTypeInterface' => $config['resource_type']['entity_class'],
                        'EdcomsCMS\ResourcesBundle\Model\ResourceTopicInterface' => $config['resource_topic']['entity_class'],
                        'EdcomsCMS\ResourcesBundle\Model\AgeGroupInterface' => $config['age_group']['entity_class'],
                        'EdcomsCMS\ResourcesBundle\Model\ResourceActivityInterface' => $config['resource_activity']['entity_class'],
                    )
                )
            ));
        }
    }


    /**
     * @param $bundles
     * @param ContainerBuilder $container
     * @throws \Exception
     */
    private function prependEdcomsContentConfig($bundles, ContainerBuilder $container, $config){

        if (isset($bundles['EdcomsCMSContentBundle'])) {
            $container->prependExtensionConfig('edcoms_cms_content', array(
                'structure' => [
                    'additional_context_classes' => $this->generateAdditionalContextClassesConfig($config)
                ]
            ));
        }
    }

    /**
     * @param $config
     *
     * @return array
     */
    private function generateAdditionalContextClassesConfig($config){
        $additionalContextClasses = [
            'resource_subject',
            'resource',
            'resource_type',
            'resource_topic',
            'age_group',
            'video_resource',
            'resource_activity'
        ];

        $additionalContextClassesConfig= [];

        foreach ($additionalContextClasses as $additionalContextClass){
            if( $config[$additionalContextClass]['used_as_context']==true){
                $additionalContextClassesConfig[$config[$additionalContextClass]['name']] =  [
                    'context' => $config[$additionalContextClass]['entity_class'],
                    'label'   => $config[$additionalContextClass]['label'],
                    'form'    => $config[$additionalContextClass]['form'],
                    'name'    => $config[$additionalContextClass]['name'],
                    'context_class' => $config[$additionalContextClass]['context_class']
                ];
            }
        }
        return $additionalContextClassesConfig;
    }

}
