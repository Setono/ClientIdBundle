<?php

declare(strict_types=1);

namespace Setono\ClientIdBundle\DependencyInjection\Compiler;

use Setono\ClientIdBundle\Doctrine\Type\ClientIdType;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class RegisterClientIdTypePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasParameter('doctrine.dbal.connection_factory.types')) {
            return;
        }

        /** @var array<string, mixed> $typeDefinition */
        $typeDefinition = $container->getParameter('doctrine.dbal.connection_factory.types');

        if (!isset($typeDefinition['client_id'])) {
            $typeDefinition['client_id'] = [
                'class' => ClientIdType::class,
                'commented' => null,
            ];
        }

        $container->setParameter('doctrine.dbal.connection_factory.types', $typeDefinition);
    }
}
