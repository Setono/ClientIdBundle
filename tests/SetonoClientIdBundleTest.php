<?php

declare(strict_types=1);

namespace Setono\ClientIdBundle\Tests;

use Nyholm\BundleTest\TestKernel;
use Setono\ClientId\Cookie\Adapter\SymfonyCookieReader;
use Setono\ClientId\Cookie\CookieReaderInterface;
use Setono\ClientId\Generator\ClientIdGeneratorInterface;
use Setono\ClientId\Generator\UuidClientIdGenerator;
use Setono\ClientId\Provider\CachedClientIdProvider;
use Setono\ClientId\Provider\ClientIdProviderInterface;
use Setono\ClientId\Provider\CookieBasedClientIdProvider;
use Setono\ClientIdBundle\Doctrine\Type\ClientIdType;
use Setono\ClientIdBundle\EventListener\SaveClientIdSubscriber;
use Setono\ClientIdBundle\SetonoClientIdBundle;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelInterface;

final class SetonoClientIdBundleTest extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }

    protected function getBundleClass(): string
    {
        return SetonoClientIdBundle::class;
    }

    protected static function createKernel(array $options = []): KernelInterface
    {
        /**
         * @var TestKernel $kernel
         */
        $kernel = parent::createKernel($options);
        $kernel->addTestBundle(SetonoClientIdBundle::class);
        $kernel->addTestCompilerPass(new class() implements CompilerPassInterface {
            public function process(ContainerBuilder $container): void
            {
                foreach ($container->getDefinitions() as $id => $definition) {
                    if (str_starts_with($id, 'setono')) {
                        $definition->setPublic(true);
                    }
                }

                foreach ($container->getAliases() as $id => $alias) {
                    if (str_starts_with($id, 'setono')) {
                        $alias->setPublic(true);
                    }
                }
            }
        });
        $kernel->handleOptions($options);

        return $kernel;
    }

    /**
     * @test
     */
    public function it_has_services(): void
    {
        $container = self::getContainer();

        $services = [
            // cookie.xml
            'setono_client_id.cookie.adapter.symfony_cookie_reader' => [
                'class' => SymfonyCookieReader::class,
                'interface' => CookieReaderInterface::class,
            ],

            // event_listener.xml
            'setono_client_id.event_listener.save_client_id_subscriber' => [
                'class' => SaveClientIdSubscriber::class,
                'interface' => EventSubscriberInterface::class,
            ],

            // generator.xml
            ClientIdGeneratorInterface::class => [
                'class' => UuidClientIdGenerator::class,
                'interface' => ClientIdGeneratorInterface::class,
            ],

            'setono_client_id.generator.default' => [
                'class' => UuidClientIdGenerator::class,
                'interface' => ClientIdGeneratorInterface::class,
            ],

            // provider.xml
            ClientIdProviderInterface::class => [
                'class' => CachedClientIdProvider::class,
                'interface' => ClientIdProviderInterface::class,
            ],

            'setono_client_id.provider.default_client_id' => [
                'class' => CachedClientIdProvider::class,
                'interface' => ClientIdProviderInterface::class,
            ],

            'setono_client_id.provider.generated_client_id' => [
                'class' => CachedClientIdProvider::class,
                'interface' => ClientIdProviderInterface::class,
            ],

            'setono_client_id.provider.cookie_based_client_id' => [
                'class' => CookieBasedClientIdProvider::class,
                'interface' => ClientIdProviderInterface::class,
            ],

            'setono_client_id.provider.cached_client_id' => [
                'class' => CachedClientIdProvider::class,
                'interface' => ClientIdProviderInterface::class,
            ],
        ];

        foreach ($services as $id => $data) {
            self::assertTrue($container->has($id), sprintf('Container does not have service "%s"', $id));

            /** @var object $service */
            $service = $container->get($id);

            if (isset($data['class'])) {
                self::assertInstanceOf(
                    $data['class'],
                    $service,
                    sprintf('Service with id "%s" is not an instance of %s. It is an instance of %s', $id, $data['class'], get_class($service)),
                );
            }

            if (isset($data['interface'])) {
                self::assertInstanceOf(
                    $data['interface'],
                    $service,
                    sprintf('Service with id "%s" is not an instance of %s. It is an instance of %s', $id, $data['interface'], get_class($service)),
                );
            }
        }
    }

    /**
     * @test
     */
    public function it_adds_compiler_pass(): void
    {
        $kernel = self::bootKernel(['config' => static function (TestKernel $kernel) {
            $kernel->addTestConfig(__DIR__ . '/config/config.yaml');
        }]);
        $container = $kernel->getContainer();

        /** @psalm-suppress UndefinedDocblockClass */
        $typeDefinitions = $container->getParameter('doctrine.dbal.connection_factory.types');

        self::assertTrue($container->hasParameter('doctrine.dbal.connection_factory.types'));
        self::assertSame(['client_id' => ['class' => ClientIdType::class, 'commented' => null]], $typeDefinitions);
    }
}
