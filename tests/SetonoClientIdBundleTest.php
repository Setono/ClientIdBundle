<?php

declare(strict_types=1);

namespace Setono\ClientIdBundle\Tests;

use Nyholm\BundleTest\BaseBundleTestCase;
use Nyholm\BundleTest\CompilerPass\PublicServicePass;
use Setono\ClientId\Cookie\Adapter\SymfonyCookieReader;
use Setono\ClientId\Cookie\CookieReaderInterface;
use Setono\ClientId\Generator\ClientIdGeneratorInterface;
use Setono\ClientId\Generator\UuidClientIdGenerator;
use Setono\ClientId\Provider\CachedClientIdProvider;
use Setono\ClientId\Provider\ClientIdProviderInterface;
use Setono\ClientId\Provider\CookieBasedClientIdProvider;
use Setono\ClientIdBundle\EventListener\SaveClientIdSubscriber;
use Setono\ClientIdBundle\SetonoClientIdBundle;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class SetonoClientIdBundleTest extends BaseBundleTestCase
{
    protected function getBundleClass(): string
    {
        return SetonoClientIdBundle::class;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->addCompilerPass(new PublicServicePass('#setono.*#i'));
    }

    /**
     * @test
     */
    public function it_has_services(): void
    {
        $this->bootKernel();
        $container = $this->getContainer();

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
                    sprintf('Service with id "%s" is not an instance of %s. It is an instance of %s', $id, $data['class'], get_class($service))
                );
            }

            if (isset($data['interface'])) {
                self::assertInstanceOf(
                    $data['interface'],
                    $service,
                    sprintf('Service with id "%s" is not an instance of %s. It is an instance of %s', $id, $data['interface'], get_class($service))
                );
            }
        }
    }
}
