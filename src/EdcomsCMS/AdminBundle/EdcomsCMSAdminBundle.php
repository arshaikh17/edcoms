<?php

namespace EdcomsCMS\AdminBundle;

use EdcomsCMS\AdminBundle\DependencyInjection\Compiler\AddFormFieldsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class EdcomsCMSAdminBundle extends Bundle
{

    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new AddFormFieldsPass());
    }

}
