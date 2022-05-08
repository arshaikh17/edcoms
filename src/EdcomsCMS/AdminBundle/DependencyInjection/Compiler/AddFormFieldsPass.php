<?php

namespace EdcomsCMS\AdminBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;


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

        $resources[] = 'EdcomsCMSAdminBundle:Form:fields.html.twig';
        $resources[] = 'InfiniteFormBundle::form_theme.html.twig';

        $container->setParameter('twig.form.resources', $resources);
    }
}
