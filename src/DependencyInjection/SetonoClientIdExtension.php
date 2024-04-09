<?php

declare(strict_types=1);

namespace Setono\ClientIdBundle\DependencyInjection;

use Composer\InstalledVersions;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

final class SetonoClientIdExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        if (InstalledVersions::isInstalled('setono/client-bundle', false)) {
            $loader->load('services/conditional/provider.xml');
        } else {
            $loader->load('services/conditional/event_listener.xml');
        }
    }
}
