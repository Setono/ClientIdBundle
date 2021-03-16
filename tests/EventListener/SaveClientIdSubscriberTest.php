<?php

declare(strict_types=1);

namespace Setono\ClientIdBundle\Tests\EventListener;

use PHPUnit\Framework\TestCase;
use Setono\ClientId\ClientId;
use Setono\ClientId\Provider\ClientIdProviderInterface;
use Setono\ClientIdBundle\EventListener\SaveClientIdSubscriber;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Kernel;

/**
 * @covers \Setono\ClientIdBundle\EventListener\SaveClientIdSubscriber
 */
final class SaveClientIdSubscriberTest extends TestCase
{
    /**
     * @test
     */
    public function it_saves(): void
    {
        $kernel = new class() extends Kernel {
            use MicroKernelTrait;

            public function __construct()
            {
                parent::__construct('test', true);
            }
        };

        $clientIdProvider = new class() implements ClientIdProviderInterface {
            public function get(): ClientId
            {
                return new ClientId('client_id');
            }
        };

        $response = new Response('', 301);

        $event = new ResponseEvent($kernel, new Request(), HttpKernelInterface::MASTER_REQUEST, $response);

        $subscriber = new SaveClientIdSubscriber($clientIdProvider, 'cookie_name');
        $subscriber->save($event);

        $cookies = $response->headers->getCookies();
        self::assertCount(1, $cookies);

        $cookie = $cookies[0];

        self::assertSame('cookie_name', $cookie->getName());
    }
}
