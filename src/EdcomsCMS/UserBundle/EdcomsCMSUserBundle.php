<?php

namespace EdcomsCMS\UserBundle;

use EdcomsCMS\UserBundle\DependencyInjection\Compiler\AddFormFieldsPass;
use EdcomsCMS\UserBundle\DependencyInjection\Compiler\RTBFUserExtensionPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class EdcomsCMSUserBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new AddFormFieldsPass());
        $container->addCompilerPass(new RTBFUserExtensionPass());
    }

}
