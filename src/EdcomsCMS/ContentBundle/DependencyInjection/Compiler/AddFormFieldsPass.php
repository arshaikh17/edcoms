<?php

namespace EdcomsCMS\ContentBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;


class AddFormFieldsPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $resources = array();
        if ($container->hasParameter('twig.form.resources')) {
            $resources = $container->getParameter('twig.form.resources');
        }

        $resources[] = 'EdcomsCMSContentBundle:Admin/Form:fields.html.twig';

        $container->setParameter('twig.form.resources', $resources);
    }
}
