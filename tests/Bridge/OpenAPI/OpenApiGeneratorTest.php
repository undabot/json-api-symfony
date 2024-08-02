<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Tests\Bridge\OpenAPI;

use PHPUnit\Framework\TestCase;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\Api;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\OpenApiDefinition;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\OpenApiGenerator;

/**
 * @internal
 *
 * @coversNothing
 *
 * @small
 */
final class OpenApiGeneratorTest extends TestCase
{
    private OpenApiGenerator $service;

    protected function setUp(): void
    {
        $this->service = new OpenApiGenerator();
    }

    public function testItGeneratesApiObject(): void
    {
        $api = new Api(
            'Test API',
            '1.0.0',
            'Example API documentation for JSON:API'
        );
        $openApiDefinition = $this->createMock(OpenApiDefinition::class);
        $openApiDefinition
            ->expects(self::once())
            ->method('getApi')
            ->willReturn($api);

        $expectedApi = $this->service->generateApi($openApiDefinition);
        self::assertInstanceOf(Api::class, $expectedApi);
        self::assertEquals($api, $expectedApi);
    }
}
