<?php

namespace Dsnet\Sf1SecurityBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Dsnet\Sf1SecurityBundle\DependencyInjection\Security\Factory\Sf1Factory;

class DsnetSf1SecurityBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new Sf1Factory());
    }
}
