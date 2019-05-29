<?php

declare(strict_types=1);

namespace Undabot\JsonApi\Tests\Integration\Resource\Denormalizer;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Undabot\JsonApi\Model\Resource\Resource;
use Undabot\SymfonyJsonApi\Model\Attribute\ResourceAttributesFactory;
use Undabot\SymfonyJsonApi\Model\Relationship\ResourceRelationshipsFactory;
use Undabot\SymfonyJsonApi\Resource\Denormalizer\Exception\MissingDataValueResourceDenormalizationException;
use Undabot\SymfonyJsonApi\Resource\Denormalizer\Exception\ResourceDenormalizationException;
use Undabot\SymfonyJsonApi\Resource\Denormalizer\ResourceDenormalizer;
use Undabot\SymfonyJsonApi\Resource\Model\AnnotatedResource\Annotation as JsonApi;
use Undabot\SymfonyJsonApi\Resource\Model\Metadata\Factory\ResourceMetadataFactory;

class AliasedResourceDto
{
    /**
     * @var string
     * @JsonApi\Attribute(name="name")
     */
    private $title;

    /**
     * @var string|null
     * @JsonApi\Attribute(name="description")
     */
    private $summary;

    /**
     * @var array
     * @JsonApi\ToMany(name="tags", type="tag")
     */
    private $tagIds;

    /**
     * @var string|null
     * @JsonApi\ToOne(name="owner", type="person")
     */
    private $ownerId;

    public function __construct(string $title, ?string $summary, array $tagIds, ?string $ownerId)
    {
        $this->title = $title;
        $this->summary = $summary;
        $this->tagIds = $tagIds;
        $this->ownerId = $ownerId;
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

class ResourceWithAliasesDenormalizerTest extends TestCase
{
    /** @var ResourceDenormalizer */
    private $serializer;

    protected function setUp()
    {
        parent::setUp();
        AnnotationRegistry::registerLoader('class_exists');
        $annotationReader = new AnnotationReader();
        $resourceMetadataFactory = new ResourceMetadataFactory($annotationReader);

        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $normalizer = new ObjectNormalizer($classMetadataFactory);

        $this->serializer = new ResourceDenormalizer($resourceMetadataFactory, $normalizer);
    }

    public function testSimpleResourceCanBeDenormalized()
    {
        $resource = new Resource(
            '1',
            'type',
            ResourceAttributesFactory::make()
                ->add('name', 'This is my title')
                ->add('summary', 'This is my summary')
                ->get(),
            ResourceRelationshipsFactory::make()
                ->toOne('owner', 'people', 'p1')
                ->toMany('tags', 'tags', ['t1', 't2', 't3'])
                ->get()
        );

        /** @var AliasedResourceDto $dto */
        $dto = $this->serializer->denormalize($resource, AliasedResourceDto::class);
        $this->assertInstanceOf(AliasedResourceDto::class, $dto);

        $this->assertSame('This is my title', $dto->getTitle());
        $this->assertSame('This is my summary', $dto->getSummary());
        $this->assertSame('p1', $dto->getOwnerId());
        $this->assertSame(['t1', 't2', 't3'], $dto->getTagIds());
    }

    public function testDenormalizationOfInvalidResourceResultsWithException()
    {
        $resource = new Resource(
            '1',
            'type',
            ResourceAttributesFactory::make()
                ->add('foo', 'This is my title')
                ->add('bar', 'This is my summary')
                ->get(),
            ResourceRelationshipsFactory::make()
                ->toOne('baz', 'people', 'p1')
                ->toMany('bash', 'tags', ['t1', 't2', 't3'])
                ->get()
        );

        $this->expectException(MissingDataValueResourceDenormalizationException::class);
        $this->serializer->denormalize($resource, AliasedResourceDto::class);
    }

    public function testDenormalizationOfResourceWithExtraAttributeResultsWithException()
    {
        $resource = new Resource(
            '1',
            'type',
            ResourceAttributesFactory::make()
                ->add('name', 'This is my title')
                ->add('summary', 'This is my summary')
                ->add('extra', 'extra')
                ->get(),
            ResourceRelationshipsFactory::make()
                ->toOne('owner', 'people', 'p1')
                ->toMany('tags', 'tags', ['t1', 't2', 't3'])
                ->get()
        );

        $this->expectException(ResourceDenormalizationException::class);
        $this->serializer->denormalize($resource, AliasedResourceDto::class);
    }

    public function testDenormalizationOfResourceWithExtraRelationshipResultsWithException()
    {
        $resource = new Resource(
            '1',
            'type',
            ResourceAttributesFactory::make()
                ->add('name', 'This is my title')
                ->add('summary', 'This is my summary')
                ->get(),
            ResourceRelationshipsFactory::make()
                ->toOne('owner', 'people', 'p1')
                ->toMany('tags', 'tags', ['t1', 't2', 't3'])
                ->toMany('extras', 'extra', ['e1', 'e2', 'e3'])
                ->get()
        );

        $this->expectException(ResourceDenormalizationException::class);
        $this->serializer->denormalize($resource, AliasedResourceDto::class);
    }
}