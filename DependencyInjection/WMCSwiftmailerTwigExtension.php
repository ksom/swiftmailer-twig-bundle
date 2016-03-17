<?php

namespace WMC\SwiftmailerTwigBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class WMCSwiftmailerTwigExtension extends Extension implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
    }

    /**
    * {@inheritDoc}
    */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasExtension('fos_user')) {
            $container->removeDefinition('wmc.swiftmailer_twig.fosub');
        }
    }
}
