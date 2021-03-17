<?php

declare(strict_types=1);

namespace Setono\ClientIdBundle;

use Setono\ClientIdBundle\DependencyInjection\Compiler\RegisterClientIdTypePass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class SetonoClientIdBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new RegisterClientIdTypePass());
    }
}
