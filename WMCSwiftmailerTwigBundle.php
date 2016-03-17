<?php

namespace WMC\SwiftmailerTwigBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;

class WMCSwiftmailerTwigBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        if (version_compare(Kernel::VERSION, '2.8', '<')) {
            $container->addCompilerPass($this->getContainerExtension());
        }
    }
}
