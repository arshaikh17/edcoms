<?php

namespace EdcomsCMS\SettingsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Dmishh\SettingsBundle\Manager\SettingsManagerInterface;
use Dmishh\SettingsBundle\Entity\SettingsOwnerInterface;
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;

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
        $rootNode = $treeBuilder->root('edcoms_cms_settings');
        $this->buildSettingOptionsConfig($rootNode);

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        return $treeBuilder;
    }

    public function buildSettingOptionsConfig(ArrayNodeDefinition $node){
        $node
            ->children()
                ->scalarNode('template')
                    ->defaultValue('EdcomsCMSSettingsBundle:Admin:list.html.twig')
                ->end()
                ->enumNode('serialization')
                    ->defaultValue('json')
                    ->values(['php', 'json'])
                ->end()
                ->arrayNode('categories')
                    ->scalarPrototype()->end()
                ->end()
                ->arrayNode('settings')
                    ->useAttributeAsKey('setting_name') // not really used but it is needed to tell Symfony to handle it as an associative array
                    ->prototype('array')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('type')->defaultValue('text')->end()
                            ->scalarNode('category')->defaultValue('cms')->end()
                            ->scalarNode('template')->end()
                            ->variableNode('options')
                                ->info('The options given to the form builder')
                                ->defaultValue([])
                                ->validate()
                                    ->always(function ($v) {
                                        if (!is_array($v)) {
                                            throw new InvalidTypeException();
                                        }
                                        return $v;
                                    })
                                ->end()
                            ->end()
                            ->variableNode('constraints')
                                ->info('The constraints on this option. Example, use constraits found in Symfony\Component\Validator\Constraints')
                                ->defaultValue(array())
                                ->validate()
                                ->always(function ($v) {
                                    if (!is_array($v)) {
                                        throw new InvalidTypeException();
                                    }
                                    return $v;
                                })
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }
}
