<?php

namespace EdcomsCMS\ResourcesBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AttachConfigPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $container
            ->getDefinition('edcoms.resources.service.configuration')
            ->replaceArgument(0,$container->getParameter('edcoms_cms_resources.configuration'));

        $container
            ->getDefinition('edcoms.resources.repository.resource')
            ->replaceArgument(0,$container->getParameter('edcoms_cms_resources.configuration')['base_resource']);
    }
}
