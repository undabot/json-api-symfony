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
use Undabot\SymfonyJsonApi\Resource\Model\AnnotatedResource\Annotation as JsonApi;
use Undabot\SymfonyJsonApi\Resource\Model\Metadata\Factory\ResourceMetadataFactory;
use Undabot\SymfonyJsonApi\Resource\Denormalizer\ResourceDenormalizer;

class ResourceDto
{
    /**
     * @var string
     * @JsonApi\Attribute()
     */
    private $title;

    /**
     * @var string|null
     * @JsonApi\Attribute()
     */
    private $summary;

    /**
     * @var array
     * @JsonApi\ToMany(type="tag")
     */
    private $tags;

    /**
     * @var string|null
     * @JsonApi\ToOne(type="person")
     */
    private $owner;

    public function __construct(string $title, ?string $summary, array $tags, ?string $owner)
    {
        $this->title = $title;
        $this->summary = $summary;
        $this->tags = $tags;
        $this->owner = $owner;
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

class ResourceDenormalizerTest extends TestCase
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

    public function testAliasedResourceCanBeDenormalized()
    {
        $resource = new Resource(
            '1',
            'type',
            ResourceAttributesFactory::make()
                ->add('title', 'This is my title')
                ->add('summary', 'This is my summary')
                ->get(),
            ResourceRelationshipsFactory::make()
                ->toOne('owner', 'people', 'p1')
                ->toMany('tags', 'tags', ['t1', 't2', 't3'])
                ->get()
        );

        /** @var ResourceDto $dto */
        $dto = $this->serializer->denormalize($resource, ResourceDto::class);
        $this->assertInstanceOf(ResourceDto::class, $dto);

        $this->assertSame('This is my title', $dto->getTitle());
        $this->assertSame('This is my summary', $dto->getSummary());
        $this->assertSame('p1', $dto->getOwner());
        $this->assertSame(['t1', 't2', 't3'], $dto->getTags());
    }
}
