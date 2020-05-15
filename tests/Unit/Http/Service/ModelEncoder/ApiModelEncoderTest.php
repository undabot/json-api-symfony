<?php

declare(strict_types=1);

namespace Undabot\JsonApi\Tests\Unit\Http\Service\ModelEncoder;

use Assert\AssertionFailedException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Undabot\JsonApi\Definition\Model\Resource\ResourceInterface;
use Undabot\SymfonyJsonApi\Http\Service\ModelEncoder\ApiModelEncoder;
use Undabot\SymfonyJsonApi\Model\ApiModel;
use Undabot\SymfonyJsonApi\Model\Collection\ArrayCollection;
use Undabot\SymfonyJsonApi\Service\Resource\Factory\ResourceFactory;

/**
 * @internal
 * @covers \Undabot\SymfonyJsonApi\Http\Service\ModelEncoder\ApiModelEncoder
 *
 * @small
 */
final class ApiModelEncoderTest extends TestCase
{
    /** @var MockObject */
    private $resourceFactory;

    /** @var ApiModelEncoder */
    private $apiModelEncoder;

    protected function setUp(): void
    {
        $this->resourceFactory = $this->createMock(ResourceFactory::class);

        $this->apiModelEncoder = new ApiModelEncoder($this->resourceFactory);
    }

    public function testEncodeDataWillThrowExceptionGivenCallableDoNotReturnApiModelClass(): void
    {
        $data = new \stdClass();
        $data->id = '1244323242';

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Invalid data conversion occurred. Expected instance of ApiModel, got ' . \get_class(new ArrayCollection([])));

        $this->apiModelEncoder->encodeData($data, static function ($data) {
            return new ArrayCollection([$data]);
        });
    }

    public function testEncodeDataWillReturnResourceInterfaceGivenCallableReturnsApiModelClass(): void
    {
        $data = new \stdClass();
        $data->id = '1244323242';

        $resourceInterface = $this->createMock(ResourceInterface::class);

        $this->resourceFactory->expects(static::once())->method('make')->willReturn($resourceInterface);

        $encodedData = $this->apiModelEncoder->encodeData($data, static function ($data) {
            return new DummyApiModel($data->id);
        });

        static::assertEquals($resourceInterface, $encodedData);
    }

    public function testEncodeDatasetWillThrowExceptionGivenModelTransformerDoNotReturnApiModel(): void
    {
        $data = new \stdClass();
        $data->id = '1244323242';

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Invalid data conversion occurred. Expected instance of ApiModel, got ' . \get_class(new ArrayCollection([])));

        $this->apiModelEncoder->encodeDataset([$data], static function ($data) {
            return new ArrayCollection([$data]);
        });
    }

    public function testEncodeDatasetWillReturnArrayOfResourceInterfacesGivenCallableReturnsApiModelClasses(): void
    {
        $data1 = new \stdClass();
        $data1->id = '1244323242';

        $data2 = new \stdClass();
        $data2->id = '67658856';

        $resourceInterface = $this->createMock(ResourceInterface::class);

        $this->resourceFactory->expects(static::exactly(2))->method('make')->willReturn($resourceInterface);

        $encodedDataset = $this->apiModelEncoder->encodeDataset([$data1, $data2], static function ($data) {
            return new DummyApiModel($data->id);
        });

        static::assertContainsOnlyInstancesOf(ResourceInterface::class, $encodedDataset);
    }
}

class DummyApiModel implements ApiModel
{
    private $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function getId(): string
    {
        return $this->id;
    }
}
