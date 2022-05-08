<?php

namespace EdcomsCMS\AdminBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

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
        $rootNode = $treeBuilder->root('edcoms_cms_admin');

        $this->buildAssetsConfig($rootNode);

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        return $treeBuilder;
    }

    public function buildAssetsConfig(ArrayNodeDefinition $node){
        $node
            ->children()
                ->arrayNode('assets')
                    ->children()
                        ->arrayNode('stylesheets')
                            ->prototype('scalar')
                            ->end()
                        ->end()
                        ->arrayNode('javascripts')
                            ->prototype('scalar')
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->booleanNode('legacy_video_player_snippet')
                    ->defaultTrue()
                ->end()
            ->end()
            ;
    }
}
