<?php

declare(strict_types=1);

namespace Undabot\JsonApi\Tests\Integration\Resource\Denormalizer;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Undabot\JsonApi\Implementation\Model\Resource\Resource;
use Undabot\SymfonyJsonApi\Model\ApiModel;
use Undabot\SymfonyJsonApi\Model\Resource\Annotation as JsonApi;
use Undabot\SymfonyJsonApi\Service\Resource\Builder\ResourceAttributesBuilder;
use Undabot\SymfonyJsonApi\Service\Resource\Builder\ResourceRelationshipsBuilder;
use Undabot\SymfonyJsonApi\Service\Resource\Denormalizer\ResourceDenormalizer;
use Undabot\SymfonyJsonApi\Service\Resource\Factory\ResourceMetadataFactory;
use Undabot\SymfonyJsonApi\Service\Resource\Validation\Constraint\ResourceType;

#[ResourceType(type: 'resources')]
class ResourceDto implements ApiModel
{
    public function __construct(
        private readonly string $id,
        #[JsonApi\Attribute]
        private readonly string $title,
        #[JsonApi\Attribute]
        private readonly ?string $summary,
        #[JsonApi\ToMany(type: 'tag')]
        private readonly array $tags,
        #[JsonApi\ToOne(type: 'person')]
        private readonly ?string $owner
    ) {}

    public function getId(): string
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getSummary(): ?string
    {
        return $this->summary;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function getOwner(): ?string
    {
        return $this->owner;
    }
}

/**
 * @internal
 */
#[CoversNothing]
#[Small]
final class ResourceDenormalizerTest extends TestCase
{
    /** @var ResourceDenormalizer */
    private $serializer;

    protected function setUp(): void
    {
        parent::setUp();
        $resourceMetadataFactory = new ResourceMetadataFactory();

        $classMetadataFactory = new ClassMetadataFactory(new AttributeLoader());
        $normalizer = new ObjectNormalizer($classMetadataFactory);

        $this->serializer = new ResourceDenormalizer($resourceMetadataFactory, $normalizer);
    }

    public function testAliasedResourceCanBeDenormalized(): void
    {
        $resource = new Resource(
            '1',
            'type',
            ResourceAttributesBuilder::make()
                ->add('title', 'This is my title')
                ->add('summary', 'This is my summary')
                ->get(),
            ResourceRelationshipsBuilder::make()
                ->toOne('owner', 'people', 'p1')
                ->toMany('tags', 'tags', ['t1', 't2', 't3'])
                ->get()
        );

        /** @var ResourceDto $dto */
        $dto = $this->serializer->denormalize($resource, ResourceDto::class);
        self::assertInstanceOf(ResourceDto::class, $dto);

        self::assertSame('This is my title', $dto->getTitle());
        self::assertSame('This is my summary', $dto->getSummary());
        self::assertSame('p1', $dto->getOwner());
        self::assertSame(['t1', 't2', 't3'], $dto->getTags());
    }
}
