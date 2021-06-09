<?php

declare(strict_types=1);

namespace Setono\ClientIdBundle\EventListener;

use Setono\ClientId\Provider\ClientIdProviderInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class SaveClientIdSubscriber implements EventSubscriberInterface
{
    private ClientIdProviderInterface $clientIdProvider;

    private string $cookieName;

    public function __construct(ClientIdProviderInterface $clientIdProvider, string $cookieName)
    {
        $this->clientIdProvider = $clientIdProvider;
        $this->cookieName = $cookieName;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'save',
        ];
    }

    public function save(ResponseEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();
        if ($request->isXmlHttpRequest()) {
            return;
        }

        $response = $event->getResponse();
        $response->headers->setCookie(
            Cookie::create($this->cookieName, $this->clientIdProvider->getClientId()->toString(), new \DateTime('+360 days'))
        );
    }
}
