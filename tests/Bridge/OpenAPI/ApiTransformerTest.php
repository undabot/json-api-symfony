<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Tests\Bridge\OpenAPI;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\ApiTransformer;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\Api;

/**
 * @internal
 *
 * @coversNothing
 *
 * @small
 */
#[CoversNothing]
#[Small]
final class ApiTransformerTest extends TestCase
{
    private ApiTransformer $service;

    protected function setUp(): void
    {
        $this->service = new ApiTransformer();
    }

    public function testItTransformsToJson(): void
    {
        $api = new Api(
            'Test API',
            '1.0.0',
            'Example API documentation for JSON:API'
        );
        $result = $this->service->toJson($api);
        self::assertJson($result);
    }

    public function testItTransformsToYaml(): void
    {
        $api = new Api(
            'Test API',
            '1.0.0',
            'Example API documentation for JSON:API'
        );
        $result = $this->service->toYaml($api);
        $array = Yaml::parse($result);
        self::assertIsArray($array);
    }
}
