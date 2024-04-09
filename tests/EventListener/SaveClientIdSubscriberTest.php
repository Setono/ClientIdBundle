<?php

declare(strict_types=1);

namespace Setono\ClientIdBundle\Tests\EventListener;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Setono\ClientId\ClientId;
use Setono\ClientId\Provider\ClientIdProviderInterface;
use Setono\ClientIdBundle\EventListener\SaveClientIdSubscriber;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @covers \Setono\ClientIdBundle\EventListener\SaveClientIdSubscriber
 */
final class SaveClientIdSubscriberTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @test
     */
    public function it_subscribes_to_response_event(): void
    {
        self::assertSame([KernelEvents::RESPONSE => 'save'], SaveClientIdSubscriber::getSubscribedEvents());
    }

    /**
     * @test
     */
    public function it_saves(): void
    {
        $response = new Response();

        $kernel = $this->prophesize(KernelInterface::class);

        $event = new ResponseEvent($kernel->reveal(), new Request(), HttpKernelInterface::MAIN_REQUEST, $response);

        $subscriber = new SaveClientIdSubscriber(self::getClientIdProvider(), 'cookie_name');
        $subscriber->save($event);

        $cookies = $response->headers->getCookies();
        self::assertCount(1, $cookies);

        $cookie = $cookies[0];

        self::assertSame('cookie_name', $cookie->getName());
    }

    /**
     * @test
     */
    public function it_does_not_save_cookie_if_request_is_not_a_master_request(): void
    {
        $response = new Response();

        $kernel = $this->prophesize(KernelInterface::class);

        $event = new ResponseEvent($kernel->reveal(), new Request(), HttpKernelInterface::SUB_REQUEST, $response);

        $subscriber = new SaveClientIdSubscriber(self::getClientIdProvider(), 'cookie_name');
        $subscriber->save($event);

        self::assertCount(0, $response->headers->getCookies());
    }

    /**
     * @test
     */
    public function it_does_not_save_cookie_if_request_is_an_ajax_request(): void
    {
        $response = new Response();

        $kernel = $this->prophesize(KernelInterface::class);

        $event = new ResponseEvent(
            $kernel->reveal(),
            new Request([], [], [], [], [], ['HTTP_X-Requested-With' => 'XMLHttpRequest']),
            HttpKernelInterface::MAIN_REQUEST,
            $response,
        );

        $subscriber = new SaveClientIdSubscriber(self::getClientIdProvider(), 'cookie_name');
        $subscriber->save($event);

        self::assertCount(0, $response->headers->getCookies());
    }

    private static function getClientIdProvider(): ClientIdProviderInterface
    {
        return new class() implements ClientIdProviderInterface {
            public function getClientId(): ClientId
            {
                return new ClientId('client_id');
            }
        };
    }
}
