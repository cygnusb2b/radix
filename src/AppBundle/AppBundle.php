<?php

namespace AppBundle;

use AppBundle\DependencyInjection\Compiler\TwigDatabaseLoaderPass;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AppBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new TwigDatabaseLoaderPass());
    }
}
