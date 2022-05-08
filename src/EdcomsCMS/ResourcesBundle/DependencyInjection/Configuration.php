<?php

namespace EdcomsCMS\ResourcesBundle\DependencyInjection;

use EdcomsCMS\ResourcesBundle\Admin\AgeGroupAdmin;
use EdcomsCMS\ResourcesBundle\Admin\ResourceActivityAdmin;
use EdcomsCMS\ResourcesBundle\Admin\ResourceAdmin;
use EdcomsCMS\ResourcesBundle\Admin\ResourceSubjectAdmin;
use EdcomsCMS\ResourcesBundle\Admin\ResourceTopicAdmin;
use EdcomsCMS\ResourcesBundle\Admin\ResourceTypeAdmin;
use EdcomsCMS\ResourcesBundle\Entity\AgeGroup;
use EdcomsCMS\ResourcesBundle\Entity\ResourceActivity;
use EdcomsCMS\ResourcesBundle\Entity\ResourceTopic;
use EdcomsCMS\ResourcesBundle\Entity\VideoResource;
use EdcomsCMS\ResourcesBundle\EntityContext\AgeGroupContext;
use EdcomsCMS\ResourcesBundle\EntityContext\ResourceActivityContext;
use EdcomsCMS\ResourcesBundle\EntityContext\ResourceContext;
use EdcomsCMS\ResourcesBundle\EntityContext\ResourceSubjectContext;
use EdcomsCMS\ResourcesBundle\Entity\Resource;
use EdcomsCMS\ResourcesBundle\Entity\ResourceSubject;
use EdcomsCMS\ResourcesBundle\EntityContext\ResourceTopicContext;
use EdcomsCMS\ResourcesBundle\EntityContext\ResourceTypeContext;
use EdcomsCMS\ResourcesBundle\EntityContext\VideoResourceContext;
use EdcomsCMS\ResourcesBundle\Form\Type\AgeGroupType;
use EdcomsCMS\ResourcesBundle\Form\Type\ResourceActivityType;
use EdcomsCMS\ResourcesBundle\Form\Type\ResourceSubjectType;
use EdcomsCMS\ResourcesBundle\Form\Type\ResourceTopicType;
use EdcomsCMS\ResourcesBundle\Form\Type\ResourceType;
use EdcomsCMS\ResourcesBundle\Form\Type\ResourceTypeType;
use EdcomsCMS\ResourcesBundle\Form\Type\VideoResourceType;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('edcoms_cms_resources');

        $rootNode
            ->children()
                ->scalarNode('base_resource')->defaultValue(Resource::class)->end()
                ->arrayNode('resource_subject')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('context_class')->defaultValue(ResourceSubjectContext::class)->cannotBeOverwritten()->end()
                        ->scalarNode('entity_class')->defaultValue(ResourceSubject::class)->end()
                        ->booleanNode('used_as_context')->defaultValue(false)->end()
                        ->scalarNode('label')->defaultValue('Resource subject')->end()
                        ->scalarNode('name')->defaultValue('resource_subject')->end()
                        ->scalarNode('form')->defaultValue(ResourceSubjectType::class)->end()
                        ->scalarNode('admin')->defaultValue(ResourceSubjectAdmin::class)->end()
                        ->scalarNode('default_admin_service')->defaultValue('edcoms.resources.admin.resource_subject')->cannotBeOverwritten()->end()
                        ->scalarNode('custom_admin_service')->defaultValue('')->end()
                        ->booleanNode('show_admin')->defaultValue(true)->end()
                    ->end()
                ->end()
                ->arrayNode('resource')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('context_class')->defaultValue(ResourceContext::class)->cannotBeOverwritten()->end()
                        ->scalarNode('entity_class')->defaultValue(Resource::class)->end()
                        ->booleanNode('used_as_context')->defaultValue(true)->end()
                        ->scalarNode('label')->defaultValue('Resource')->end()
                        ->scalarNode('name')->defaultValue('resource')->end()
                        ->scalarNode('form')->defaultValue(ResourceType::class)->end()
                        ->scalarNode('admin')->defaultValue(ResourceAdmin::class)->end()
                        ->scalarNode('default_admin_service')->defaultValue('edcoms.resources.admin.resource')->cannotBeOverwritten()->end()
                        ->scalarNode('custom_admin_service')->defaultValue('')->end()
                        ->booleanNode('show_admin')->defaultValue(false)->end()
                    ->end()
                ->end()
                ->arrayNode('video_resource')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('context_class')->defaultValue(ResourceContext::class)->cannotBeOverwritten()->end()
                        ->scalarNode('entity_class')->defaultValue(VideoResource::class)->end()
                        ->booleanNode('used_as_context')->defaultValue(true)->end()
                        ->scalarNode('label')->defaultValue('Video Resource')->end()
                        ->scalarNode('name')->defaultValue('video_resource')->end()
                        ->scalarNode('form')->defaultValue(VideoResourceType::class)->end()
                    ->end()
                ->end()
                ->arrayNode('resource_type')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('context_class')->defaultValue(ResourceTypeContext::class)->cannotBeOverwritten()->end()
                        ->scalarNode('entity_class')->defaultValue(\EdcomsCMS\ResourcesBundle\Entity\ResourceType::class)->end()
                        ->booleanNode('used_as_context')->defaultValue(false)->end()
                        ->scalarNode('label')->defaultValue('Resource type')->end()
                        ->scalarNode('name')->defaultValue('resource_type')->end()
                        ->scalarNode('form')->defaultValue(ResourceTypeType::class)->end()
                        ->scalarNode('admin')->defaultValue(ResourceTypeAdmin::class)->end()
                        ->scalarNode('default_admin_service')->defaultValue('edcoms.resources.admin.resource_type')->cannotBeOverwritten()->end()
                        ->scalarNode('custom_admin_service')->defaultValue('')->end()
                        ->booleanNode('show_admin')->defaultValue(true)->end()
                    ->end()
                ->end()
                ->arrayNode('resource_topic')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('context_class')->defaultValue(ResourceTopicContext::class)->cannotBeOverwritten()->end()
                        ->scalarNode('entity_class')->defaultValue( ResourceTopic::class)->end()
                        ->booleanNode('used_as_context')->defaultValue(false)->end()
                        ->scalarNode('label')->defaultValue('Resource topic')->end()
                        ->scalarNode('name')->defaultValue('resource_topic')->end()
                        ->scalarNode('form')->defaultValue(ResourceTopicType::class)->end()
                        ->scalarNode('admin')->defaultValue(ResourceTopicAdmin::class)->end()
                        ->scalarNode('default_admin_service')->defaultValue('edcoms.resources.admin.resource_topic')->cannotBeOverwritten()->end()
                        ->scalarNode('custom_admin_service')->defaultValue('')->end()
                        ->booleanNode('show_admin')->defaultValue(true)->end()
                    ->end()
                ->end()
                ->arrayNode('resource_activity')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('context_class')->defaultValue(ResourceActivityContext::class)->cannotBeOverwritten()->end()
                        ->scalarNode('entity_class')->defaultValue( ResourceActivity::class)->end()
                        ->booleanNode('used_as_context')->defaultValue(false)->end()
                        ->scalarNode('label')->defaultValue('Resource activity')->end()
                        ->scalarNode('name')->defaultValue('resource_activity')->end()
                        ->scalarNode('form')->defaultValue(ResourceActivityType::class)->end()
                        ->scalarNode('admin')->defaultValue(ResourceActivityAdmin::class)->end()
                        ->scalarNode('default_admin_service')->defaultValue('edcoms.resources.admin.resource_activity')->cannotBeOverwritten()->end()
                        ->scalarNode('custom_admin_service')->defaultValue('')->end()
                        ->booleanNode('show_admin')->defaultValue(true)->end()
                    ->end()
                ->end()
                ->arrayNode('age_group')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('context_class')->defaultValue(AgeGroupContext::class)->cannotBeOverwritten()->end()
                        ->scalarNode('entity_class')->defaultValue( AgeGroup::class)->end()
                        ->booleanNode('used_as_context')->defaultValue(false)->end()
                        ->scalarNode('label')->defaultValue('Age group')->end()
                        ->scalarNode('name')->defaultValue('age_group')->end()
                        ->scalarNode('form')->defaultValue(AgeGroupType::class)->end()
                        ->scalarNode('admin')->defaultValue(AgeGroupAdmin::class)->end()
                        ->scalarNode('default_admin_service')->defaultValue('edcoms.resources.admin.age_group')->cannotBeOverwritten()->end()
                        ->scalarNode('custom_admin_service')->defaultValue('')->end()
                        ->booleanNode('show_admin')->defaultValue(true)->end()
                    ->end()
                ->end()
                ->arrayNode('filtering')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('api')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('resource_route')->defaultValue(null)->end()
                                ->arrayNode('options')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->arrayNode('batch')
                                            ->addDefaultsIfNotSet()
                                            ->children()
                                                ->scalarNode('default')->defaultValue(20)->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end()
        ;

        return $treeBuilder;
    }
}
