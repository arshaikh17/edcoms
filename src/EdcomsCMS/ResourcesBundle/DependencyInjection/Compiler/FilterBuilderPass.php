<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ResourcesBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class FilterBuilderPass implements CompilerPassInterface
{

    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition('edcoms.resources.filter_provider.builder_service');

        $filterBuilders = array();
        foreach ($container->findTaggedServiceIds('edcoms.resources.factory_builder') as $id => $tags) {
            $builderDefinition = $container->getDefinition($id);

            if (!$builderDefinition->isPublic()) {
                throw new \InvalidArgumentException(sprintf('Menu builder services must be public but "%s" is a private service.', $id));
            }

            if ($builderDefinition->isAbstract()) {
                throw new \InvalidArgumentException(sprintf('Abstract services cannot be registered as menu builders but "%s" is.', $id));
            }

            foreach ($tags as $attributes) {
                if (empty($attributes['alias'])) {
                    throw new \InvalidArgumentException(sprintf('The alias is not defined in the "edcoms.resources.factory_builder" tag for the service "%s"', $id));
                }
                if (empty($attributes['method'])) {
                    throw new \InvalidArgumentException(sprintf('The method is not defined in the "edcoms.resources.factory_builder" tag for the service "%s"', $id));
                }
                $filterBuilders[$attributes['alias']] = array($id, $attributes['method']);
            }
        }
        $definition->replaceArgument(1, $filterBuilders);
    }
}