<?php

declare(strict_types=1);

namespace Setono\ClientIdBundle\Tests\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Setono\ClientIdBundle\DependencyInjection\SetonoClientIdExtension;

/**
 * @covers \Setono\ClientIdBundle\DependencyInjection\SetonoClientIdExtension
 */
final class SetonoClientIdExtensionTest extends AbstractExtensionTestCase
{
    protected function getContainerExtensions(): array
    {
        return [
            new SetonoClientIdExtension(),
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
}
