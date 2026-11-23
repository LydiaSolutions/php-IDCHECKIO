<?php

declare(strict_types=1);

namespace idcheckio\Unit;

use Generator;
use idcheckio\ApiClient;
use idcheckio\Configuration;
use idcheckio\api\AdministrationApi;
use idcheckio\api\AnalysisApi;
use idcheckio\api\SandboxApi;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ApiClientTest extends TestCase
{
    public function testClientUsesDefaultOrExplicitConfiguration(): void
    {
        $defaultConfiguration = Configuration::getDefaultConfiguration();

        self::assertSame($defaultConfiguration, (new ApiClient())->getConfig());
        self::assertSame($defaultConfiguration, (new ApiClient(null))->getConfig());
        $configuration = new Configuration();
        self::assertSame($configuration, (new ApiClient($configuration))->getConfig());
    }

    public static function apiClasses(): Generator
    {
        yield 'administration' => [AdministrationApi::class];
        yield 'analysis' => [AnalysisApi::class];
        yield 'sandbox' => [SandboxApi::class];
    }

    #[DataProvider('apiClasses')]
    public function testApiUsesDefaultOrExplicitClient(string $apiClass): void
    {
        $configuration = Configuration::getDefaultConfiguration();
        $client = new ApiClient(new Configuration());

        self::assertSame($configuration, (new $apiClass())->getApiClient()->getConfig());
        self::assertSame($configuration, (new $apiClass(null))->getApiClient()->getConfig());
        self::assertSame($client, (new $apiClass($client))->getApiClient());
    }

    public static function mediaTypes(): Generator
    {
        yield 'no media types' => [[], null, 'application/json'];
        yield 'empty media type' => [[''], null, 'application/json'];
        yield 'non-JSON types' => [['text/plain', 'image/png'], 'text/plain,image/png', 'text/plain,image/png'];
        yield 'JSON with a charset' => [['text/plain', 'Application/JSON; charset=UTF-8'], 'application/json', 'application/json'];
    }

    #[DataProvider('mediaTypes')]
    public function testSelectsAcceptAndContentType(array $types, ?string $accept, string $contentType): void
    {
        $client = new ApiClient();
        self::assertSame($accept, $client->selectHeaderAccept($types));
        self::assertSame($contentType, $client->selectHeaderContentType($types));
    }

    public function testApiKeyPrefixRequiresAKey(): void
    {
        $configuration = new Configuration();
        $client = new ApiClient($configuration);

        $configuration->setApiKeyPrefix('Authorization', 'Bearer');
        self::assertNull($client->getApiKeyWithPrefix('Authorization'));

        $configuration->setApiKey('X-API-Key', 'fixture-key');
        self::assertSame('fixture-key', $client->getApiKeyWithPrefix('X-API-Key'));

        $configuration->setApiKey('Authorization', 'fixture-token');
        self::assertSame('Bearer fixture-token', $client->getApiKeyWithPrefix('Authorization'));
    }

    public function testResponseHeadersPreserveRepeatedValuesAndHandleBlankLines(): void
    {
        $client = new class extends ApiClient {
            public function parseHeaders(string $rawHeaders): array
            {
                return parent::httpParseHeaders($rawHeaders);
            }
        };

        $rawHeaders = "HTTP/1.1 200 OK\r\nContent-Type: application/json\r\n"
            . "Location: https://example.test/result\r\nX-Folded: first\r\n\tsecond\r\n"
            . "Set-Cookie: session=abc\r\nSet-Cookie: preference=xyz\r\nSet-Cookie: flag=1\r\n\r\n";

        self::assertSame([
            'HTTP/1.1 200 OK',
            'Content-Type' => 'application/json',
            'Location' => 'https://example.test/result',
            'X-Folded' => "first\r\n\tsecond",
            'Set-Cookie' => ['session=abc', 'preference=xyz', 'flag=1'],
        ], $client->parseHeaders($rawHeaders));
    }
}
