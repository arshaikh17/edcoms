<?php

namespace EdcomsCMS\ContentBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;


class AttachConfigPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $container
            ->getDefinition('edcoms.content.service.configuration')
            ->replaceArgument(0,$container->getParameter('edcoms_cms_content.configuration'));

        $container->getParameterBag()->remove('edcoms_cms_content.configuration');

        // remove the definition for the service edcoms.logger.slack if not used by the application
        if (!$container->getParameterBag()->has('edcoms_logger_slack')) {
            $container->removeDefinition('edcoms.logger.slack');
        }
    }
}
