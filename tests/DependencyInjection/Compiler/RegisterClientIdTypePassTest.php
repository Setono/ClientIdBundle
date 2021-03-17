<?php

declare(strict_types=1);

namespace Setono\ClientIdBundle\Tests\DependencyInjection\Compiler;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Setono\ClientIdBundle\DependencyInjection\Compiler\RegisterClientIdTypePass;
use Setono\ClientIdBundle\Doctrine\Type\ClientIdType;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @covers \Setono\ClientIdBundle\DependencyInjection\Compiler\RegisterClientIdTypePass
 */
final class RegisterClientIdTypePassTest extends AbstractCompilerPassTestCase
{
    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new RegisterClientIdTypePass());
    }

    /**
     * @test
     */
    public function it_updates_parameter(): void
    {
        $this->setParameter('doctrine.dbal.connection_factory.types', []);

        $this->compile();

        self::assertSame(
            ['client_id' => ['class' => ClientIdType::class, 'commented' => null]],
            $this->container->getParameter('doctrine.dbal.connection_factory.types')
        );
    }
}
