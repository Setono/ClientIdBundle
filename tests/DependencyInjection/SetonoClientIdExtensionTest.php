<?php

declare(strict_types=1);

namespace Setono\ClientIdBundle\Tests\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Setono\ClientIdBundle\DependencyInjection\SetonoClientIdExtension;
use Setono\ClientIdBundle\Doctrine\Type\ClientIdType;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

/**
 * @covers \Setono\ClientIdBundle\DependencyInjection\SetonoClientIdExtension
 */
final class SetonoClientIdExtensionTest extends AbstractExtensionTestCase
{
    protected function getContainerExtensions(): array
    {
        $dummyDoctrineExtension = new class() implements ExtensionInterface {
            public function load(array $configs, ContainerBuilder $container): void
            {
            }

            public function getNamespace(): string
            {
                return 'http://example.org/schema/dic/' . $this->getAlias();
            }

            public function getXsdValidationBasePath(): bool
            {
                return false;
            }

            public function getAlias(): string
            {
                return 'doctrine';
            }
        };

        return [
            new SetonoClientIdExtension(),
            $dummyDoctrineExtension,
        ];
    }

    /**
     * @test
     */
    public function it_can_load(): void
    {
        $this->load();

        self::assertTrue(true);
    }

    /**
     * @test
     */
    public function it_registers_event_listeners_with_tags(): void
    {
        $this->load();

        $this->assertContainerBuilderHasServiceDefinitionWithTag('setono_client_id.event_listener.save_client_id_subscriber', 'kernel.event_subscriber');
    }

    /**
     * @test
     */
    public function it_defines_doctrine_config(): void
    {
        $this->load();

        self::assertSame([
            [
                'dbal' => [
                    'types' => [
                        'client_id' => ClientIdType::class,
                    ],
                ],
            ],
        ], $this->container->getExtensionConfig('doctrine'));
    }
}
