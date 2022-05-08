<?php

namespace EdcomsCMS\ContentBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('edcoms_cms_content');
        $rootNode
            ->children()
                ->scalarNode('email')
                    ->isRequired(true)
                ->end()
                ->booleanNode('show_visible_checkbox')->defaultValue(false)->end()
                ->arrayNode('structure')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('context_enabled')->defaultValue(false)->end()
                        ->arrayNode('additional_context_classes')
                            ->useAttributeAsKey('id')
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('context')->isRequired()->end()
                                    ->scalarNode('label')->defaultValue(null)->end()
                                    ->scalarNode('name')->defaultValue(null)->end()
                                    ->scalarNode('form')->defaultValue(null)->end()
                                    ->scalarNode('context_class')->defaultValue(null)->end()
                                ->end()
                            ->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('cdn')
                ->addDefaultsIfNotSet()
                ->children()
                    ->booleanNode('enabled')->defaultValue(false)->end()
                    ->scalarNode('cdn_host')->defaultValue(null)->end()
                ->end()
            ->end()
        ;

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        return $treeBuilder;
    }
}
