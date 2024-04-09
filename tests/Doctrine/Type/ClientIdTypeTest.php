<?php

declare(strict_types=1);

namespace Setono\ClientIdBundle\Tests\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use PHPUnit\Framework\TestCase;
use Setono\ClientId\ClientId;
use Setono\ClientIdBundle\Doctrine\Type\ClientIdType;

final class ClientIdTypeTest extends TestCase
{
    private const DUMMY_CLIENT_ID = '9f755235-5a2d-4aba-9605-e9962b312e50';

    private AbstractPlatform $platform;

    private ClientIdType $type;

    public static function setUpBeforeClass(): void
    {
        if (Type::hasType('client_id')) {
            Type::overrideType('client_id', ClientIdType::class);
        } else {
            Type::addType('client_id', ClientIdType::class);
        }
    }

    protected function setUp(): void
    {
        $this->platform = $this->createMock(AbstractPlatform::class);
        $this->platform
            ->method('getVarcharTypeDeclarationSQL')
            ->willReturn('DUMMYVARCHAR()');

        /** @psalm-suppress PropertyTypeCoercion */
        $this->type = Type::getType('client_id');
    }

    /**
     * @test
     */
    public function it_converts_object_to_database_value(): void
    {
        $clientId = new ClientId(self::DUMMY_CLIENT_ID);

        $expected = $clientId->toString();
        $actual = $this->type->convertToDatabaseValue($clientId, $this->platform);

        self::assertSame($expected, $actual);
    }

    /**
     * @test
     */
    public function it_converts_string_to_database_value(): void
    {
        $actual = $this->type->convertToDatabaseValue(self::DUMMY_CLIENT_ID, $this->platform);

        self::assertSame(self::DUMMY_CLIENT_ID, $actual);
    }

    /**
     * @test
     */
    public function it_throws_exception_when_trying_to_convert_unsupported_type_for_database_value(): void
    {
        $this->expectException(ConversionException::class);
        $this->expectExceptionMessage('Could not convert PHP value of type stdClass to type client_id. Expected one of the following types: null, string, Setono\ClientId\ClientId');

        $this->type->convertToDatabaseValue(new \stdClass(), $this->platform);
    }

    /**
     * @test
     */
    public function it_returns_null_for_database_value_when_null_is_given(): void
    {
        self::assertNull($this->type->convertToDatabaseValue(null, $this->platform));
    }

    /**
     * @test
     */
    public function it_converts_string_to_php_value(): void
    {
        $expected = (new ClientId(self::DUMMY_CLIENT_ID))->toString();
        $actual = $this->type->convertToPHPValue(self::DUMMY_CLIENT_ID, $this->platform);

        self::assertInstanceOf(ClientId::class, $actual);
        self::assertSame($expected, $actual->toString());
    }

    /**
     * @test
     */
    public function it_throws_exception_when_trying_to_convert_unsupported_type_for_php_value(): void
    {
        $this->expectException(ConversionException::class);
        $this->expectExceptionMessage('Could not convert PHP value of type stdClass to type client_id. Expected one of the following types: null, string, Setono\ClientId\ClientId');

        $this->type->convertToPHPValue(new \stdClass(), $this->platform);
    }

    /**
     * @test
     */
    public function it_returns_null_for_php_value_when_null_is_given(): void
    {
        self::assertNull($this->type->convertToPHPValue(null, $this->platform));
    }

    /**
     * @test
     */
    public function it_returns_same_client_id_if_client_id_is_given_for_php_value(): void
    {
        $clientId = new ClientId(self::DUMMY_CLIENT_ID);
        self::assertSame($clientId, $this->type->convertToPHPValue($clientId, $this->platform));
    }

    /**
     * @test
     */
    public function it_returns_name(): void
    {
        self::assertSame('client_id', $this->type->getName());
    }

    /**
     * @test
     */
    public function it_returns_sql_declaration(): void
    {
        self::assertEquals('DUMMYVARCHAR()', $this->type->getSqlDeclaration(['length' => 36], $this->platform));
    }

    /**
     * @test
     */
    public function it_requires_sql_comment_hint(): void
    {
        self::assertTrue($this->type->requiresSQLCommentHint($this->platform));
    }
}
