<?php

declare(strict_types=1);

namespace Setono\ClientIdBundle\DependencyInjection\Compiler;

use Setono\ClientIdBundle\Doctrine\Type\ClientIdType;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Webmozart\Assert\Assert;

final class RegisterClientIdTypePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasParameter('doctrine.dbal.connection_factory.types')) {
            return;
        }

        /** @psalm-suppress UndefinedDocblockClass */
        $typeDefinitions = $container->getParameter('doctrine.dbal.connection_factory.types');
        Assert::isArray($typeDefinitions);

        if (!isset($typeDefinitions['client_id'])) {
            $typeDefinitions['client_id'] = [
                'class' => ClientIdType::class,
                'commented' => null,
            ];
        }

        $container->setParameter('doctrine.dbal.connection_factory.types', $typeDefinitions);
    }
}
