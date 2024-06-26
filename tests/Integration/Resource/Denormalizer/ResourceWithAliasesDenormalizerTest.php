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
use Undabot\SymfonyJsonApi\Service\Resource\Denormalizer\ResourceDenormalizer;
use Undabot\SymfonyJsonApi\Service\Resource\Factory\ResourceMetadataFactory;
use Undabot\SymfonyJsonApi\Service\Resource\Validation\Constraint\ResourceType;

/**
 * @ResourceType(type="resources")
 */
class AliasedResourceDto implements ApiModel
{
    public function __construct(
        public string $id,
        /** @JsonApi\Attribute(name="name") */
        public string $title,
        /** @JsonApi\Attribute(name="description") */
        public ?string $summary,
        /** @JsonApi\ToMany(name="tags", type="tag") */
        public array $tagIds,
        /** @JsonApi\ToOne(name="owner", type="person") */
        public ?string $ownerId
    ) {
    }
}

/**
 * @internal
 * @covers \Undabot\SymfonyJsonApi\Service\Resource\Denormalizer\ResourceDenormalizer
 *
 * @small
 */
final class ResourceWithAliasesDenormalizerTest extends TestCase
{
    private ResourceDenormalizer $serializer;

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

        static::assertSame('This is my title', $dto->title);
        static::assertSame('This is my summary', $dto->summary);
        static::assertSame('p1', $dto->ownerId);
        static::assertSame(['t1', 't2', 't3'], $dto->tagIds);
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

    public function testDenormalizeWillReturnCorrectApiModelWithExtraAttributesIgnored(): void
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

        $model = $this->serializer->denormalize($resource, AliasedResourceDto::class);
        static::assertInstanceOf(AliasedResourceDto::class, $model);
        static::assertObjectNotHasProperty('extra', $model);
    }

    public function testDenormalizeWillReturnCorrectApiModelWithExtraRelationshipsIgnored(): void
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

        $model = $this->serializer->denormalize($resource, AliasedResourceDto::class);
        static::assertInstanceOf(AliasedResourceDto::class, $model);
        static::assertObjectNotHasProperty('extras', $model);
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
        static::assertNull($dto->ownerId);
    }
}
