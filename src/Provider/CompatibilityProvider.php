<?php

declare(strict_types=1);

namespace Setono\ClientIdBundle\Provider;

use Setono\ClientBundle\Context\ClientContextInterface;
use Setono\ClientId\ClientId;
use Setono\ClientId\Provider\ClientIdProviderInterface;

final class CompatibilityProvider implements ClientIdProviderInterface
{
    private ClientContextInterface $clientContext;

    public function __construct(ClientContextInterface $clientContext)
    {
        $this->clientContext = $clientContext;
    }

    public function getClientId(): ClientId
    {
        return new ClientId($this->clientContext->getClient()->id);
    }
}
