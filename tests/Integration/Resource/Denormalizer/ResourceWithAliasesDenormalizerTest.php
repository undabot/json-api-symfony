<?php

declare(strict_types=1);

namespace Undabot\JsonApi\Tests\Integration\Resource\Denormalizer;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Undabot\JsonApi\Implementation\Model\Resource\Resource;
use Undabot\SymfonyJsonApi\Model\ApiModel;
use Undabot\SymfonyJsonApi\Model\Resource\Annotation as JsonApi;
use Undabot\SymfonyJsonApi\Service\Resource\Builder\ResourceAttributesBuilder;
use Undabot\SymfonyJsonApi\Service\Resource\Builder\ResourceRelationshipsBuilder;
use Undabot\SymfonyJsonApi\Service\Resource\Denormalizer\Exception\MissingDataValueResourceDenormalizationException;
use Undabot\SymfonyJsonApi\Service\Resource\Denormalizer\Exception\ResourceDenormalizationException;
use Undabot\SymfonyJsonApi\Service\Resource\Denormalizer\ResourceDenormalizer;
use Undabot\SymfonyJsonApi\Service\Resource\Factory\ResourceMetadataFactory;
use Undabot\SymfonyJsonApi\Service\Resource\Validation\Constraint\ResourceType;

/**
 * @ResourceType(type="resources")
 */
class AliasedResourceDto implements ApiModel
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     * @JsonApi\Attribute(name="name")
     */
    private $title;

    /**
     * @var null|string
     * @JsonApi\Attribute(name="description")
     */
    private $summary;

    /**
     * @var array
     * @JsonApi\ToMany(name="tags", type="tag")
     */
    private $tagIds;

    /**
     * @var null|string
     * @JsonApi\ToOne(name="owner", type="person")
     */
    private $ownerId;

    public function __construct(string $id, string $title, ?string $summary, array $tagIds, ?string $ownerId)
    {
        $this->id = $id;
        $this->title = $title;
        $this->summary = $summary;
        $this->tagIds = $tagIds;
        $this->ownerId = $ownerId;
    }

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

    public function getTagIds(): array
    {
        return $this->tagIds;
    }

    public function getOwnerId(): ?string
    {
        return $this->ownerId;
    }
}

/**
 * @internal
 * @coversNothing
 *
 * @small
 */
final class ResourceWithAliasesDenormalizerTest extends TestCase
{
    /** @var ResourceDenormalizer */
    private $serializer;

    protected function setUp(): void
    {
        parent::setUp();
        AnnotationRegistry::registerLoader('class_exists');
        $annotationReader = new AnnotationReader();
        $resourceMetadataFactory = new ResourceMetadataFactory($annotationReader);

        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $normalizer = new ObjectNormalizer($classMetadataFactory);

        $this->serializer = new ResourceDenormalizer($resourceMetadataFactory, $normalizer);
    }

    public function testSimpleResourceCanBeDenormalized(): void
    {
        $resource = new Resource(
            '1',
            'type',
            ResourceAttributesBuilder::make()
                ->add('name', 'This is my title')
                ->add('summary', 'This is my summary')
                ->get(),
            ResourceRelationshipsBuilder::make()
                ->toOne('owner', 'people', 'p1')
                ->toMany('tags', 'tags', ['t1', 't2', 't3'])
                ->get()
        );

        /** @var AliasedResourceDto $dto */
        $dto = $this->serializer->denormalize($resource, AliasedResourceDto::class);
        static::assertInstanceOf(AliasedResourceDto::class, $dto);

        static::assertSame('This is my title', $dto->getTitle());
        static::assertSame('This is my summary', $dto->getSummary());
        static::assertSame('p1', $dto->getOwnerId());
        static::assertSame(['t1', 't2', 't3'], $dto->getTagIds());
    }

    public function testDenormalizationOfInvalidResourceResultsWithException(): void
    {
        $resource = new Resource(
            '1',
            'type',
            ResourceAttributesBuilder::make()
                ->add('foo', 'This is my title')
                ->add('bar', 'This is my summary')
                ->get(),
            ResourceRelationshipsBuilder::make()
                ->toOne('baz', 'people', 'p1')
                ->toMany('bash', 'tags', ['t1', 't2', 't3'])
                ->get()
        );

        $this->expectException(MissingDataValueResourceDenormalizationException::class);
        $this->serializer->denormalize($resource, AliasedResourceDto::class);
    }

    public function testDenormalizationOfResourceWithExtraAttributeResultsWithException(): void
    {
        $resource = new Resource(
            '1',
            'type',
            ResourceAttributesBuilder::make()
                ->add('name', 'This is my title')
                ->add('summary', 'This is my summary')
                ->add('extra', 'extra')
                ->get(),
            ResourceRelationshipsBuilder::make()
                ->toOne('owner', 'people', 'p1')
                ->toMany('tags', 'tags', ['t1', 't2', 't3'])
                ->get()
        );

        $this->expectException(ResourceDenormalizationException::class);
        $this->serializer->denormalize($resource, AliasedResourceDto::class);
    }

    public function testDenormalizationOfResourceWithExtraRelationshipResultsWithException(): void
    {
        $resource = new Resource(
            '1',
            'type',
            ResourceAttributesBuilder::make()
                ->add('name', 'This is my title')
                ->add('summary', 'This is my summary')
                ->get(),
            ResourceRelationshipsBuilder::make()
                ->toOne('owner', 'people', 'p1')
                ->toMany('tags', 'tags', ['t1', 't2', 't3'])
                ->toMany('extras', 'extra', ['e1', 'e2', 'e3'])
                ->get()
        );

        $this->expectException(ResourceDenormalizationException::class);
        $this->serializer->denormalize($resource, AliasedResourceDto::class);
    }

    public function testResourceWithAliasedOptionalToOneRelationshipCanBeDenormalized(): void
    {
        $resource = new Resource(
            '1',
            'type',
            ResourceAttributesBuilder::make()
                ->add('name', 'This is my title')
                ->add('summary', 'This is my summary')
                ->get(),
            ResourceRelationshipsBuilder::make()
                ->toOne('owner', 'people', null)
                ->toMany('tags', 'tags', ['t1', 't2', 't3'])
                ->get()
        );

        /** @var AliasedResourceDto $dto */
        $dto = $this->serializer->denormalize($resource, AliasedResourceDto::class);
        static::assertInstanceOf(AliasedResourceDto::class, $dto);
        static::assertNull($dto->getOwnerId());
    }
}
