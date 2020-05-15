<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Tests\Unit\Http\Service\ParamConverter;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Undabot\JsonApi\Definition\Exception\Request\ClientGeneratedIdIsNotAllowedException;
use Undabot\JsonApi\Definition\Model\Request\CreateResourceRequestInterface;
use Undabot\SymfonyJsonApi\Http\Service\Factory\RequestFactory;
use Undabot\SymfonyJsonApi\Http\Service\ParamConverter\JsonApiRequestParamConverter;

/**
 * @internal
 * @coversNothing
 *
 * @small
 */
final class JsonApiRequestParamConverterTest extends TestCase
{
    /** @var MockObject */
    private $requestFactoryMock;
    /** @var JsonApiRequestParamConverter */
    private $jsonApiRequestParamConverter;

    protected function setUp(): void
    {
        $this->requestFactoryMock = $this->createMock(RequestFactory::class);

        $this->jsonApiRequestParamConverter = new JsonApiRequestParamConverter($this->requestFactoryMock);
    }

    /** @dataProvider paramConverterConfigPreventsClientGeneratedIDs */
    public function testParamConvertHandlesCreateResourceRequestsWithClientProvidedResourceIds(array $paramConverterConfiguration): void
    {
        $request = $this->createMock(Request::class);

        $config = $this->createMock(ParamConverter::class);

        $this->expectException(ClientGeneratedIdIsNotAllowedException::class);

        $config->expects(static::once())
            ->method('getName')
            ->willReturn('');

        $config->expects(static::once())
            ->method('getClass')
            ->willReturn(CreateResourceRequestInterface::class);

        $config->expects(static::once())
            ->method('getOptions')
            ->willReturn(
                $paramConverterConfiguration
            );

        $this->requestFactoryMock
            ->expects(static::once())
            ->method('requestResourceHasClientSideGeneratedId')
            ->willReturn(true);

        $this->jsonApiRequestParamConverter->apply($request, $config);
    }

    public function paramConverterConfigPreventsClientGeneratedIDs(): array
    {
        return [
            'does not support client generated ID' => [
                [JsonApiRequestParamConverter::OPTION_CLIENT_GENERATED_IDS => false],
            ],
            'default empty configuration' => [
                [],
            ],
        ];
    }
}
