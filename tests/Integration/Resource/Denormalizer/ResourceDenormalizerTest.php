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
use Undabot\SymfonyJsonApi\Service\Resource\Denormalizer\ResourceDenormalizer;
use Undabot\SymfonyJsonApi\Service\Resource\Factory\ResourceMetadataFactory;
use Undabot\SymfonyJsonApi\Service\Resource\Validation\Constraint\ResourceType;

/**
 * @ResourceType(type="resources")
 */
class ResourceDto implements ApiModel
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     * @JsonApi\Attribute
     */
    private $title;

    /**
     * @var null|string
     * @JsonApi\Attribute
     */
    private $summary;

    /**
     * @var array
     * @JsonApi\ToMany(type="tag")
     */
    private $tags;

    /**
     * @var null|string
     * @JsonApi\ToOne(type="person")
     */
    private $owner;

    public function __construct(string $id, string $title, ?string $summary, array $tags, ?string $owner)
    {
        $this->id = $id;
        $this->title = $title;
        $this->summary = $summary;
        $this->tags = $tags;
        $this->owner = $owner;
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
 * @coversNothing
 *
 * @small
 */
final class ResourceDenormalizerTest extends TestCase
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
        static::assertInstanceOf(ResourceDto::class, $dto);

        static::assertSame('This is my title', $dto->getTitle());
        static::assertSame('This is my summary', $dto->getSummary());
        static::assertSame('p1', $dto->getOwner());
        static::assertSame(['t1', 't2', 't3'], $dto->getTags());
    }
}
