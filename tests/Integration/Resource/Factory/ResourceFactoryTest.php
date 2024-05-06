<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Tests\Integration\Resource\Factory;

use Doctrine\Common\Annotations\AnnotationReader;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Undabot\JsonApi\Definition\Model\Resource\Relationship\Data\ToManyRelationshipDataInterface;
use Undabot\JsonApi\Definition\Model\Resource\Relationship\Data\ToOneRelationshipDataInterface;
use Undabot\JsonApi\Definition\Model\Resource\Relationship\RelationshipInterface;
use Undabot\JsonApi\Definition\Model\Resource\ResourceInterface;
use Undabot\JsonApi\Implementation\Model\Resource\ResourceIdentifier;
use Undabot\SymfonyJsonApi\Model\ApiModel;
use Undabot\SymfonyJsonApi\Model\Resource\Annotation as JsonApi;
use Undabot\SymfonyJsonApi\Model\Resource\FlatResource;
use Undabot\SymfonyJsonApi\Service\Resource\Factory\ResourceFactory;
use Undabot\SymfonyJsonApi\Service\Resource\Factory\ResourceMetadataFactory;
use Undabot\SymfonyJsonApi\Service\Resource\Validation\Constraint\ResourceType;
use Undabot\SymfonyJsonApi\Service\Resource\Validation\ResourceValidationViolations;
use Undabot\SymfonyJsonApi\Service\Resource\Validation\ResourceValidator;

/**
 * @internal
 *
 * @covers \Undabot\SymfonyJsonApi\Service\Resource\Factory\ResourceFactory
 *
 * @small
 */
final class ResourceFactoryTest extends TestCase
{
    private ResourceFactory $resourceFactory;
    private bool $shouldValidateReadModel = false;
    private MockObject $validatorMock;
    private ResourceMetadataFactory $resourceMetadataFactory;
    private ResourceValidator $resourceValidator;

    protected function setUp(): void
    {
        parent::setUp();
        $annotationReader = new AnnotationReader();
        $this->resourceMetadataFactory = new ResourceMetadataFactory($annotationReader);
        $this->validatorMock = $this->createMock(ValidatorInterface::class);
        $this->resourceValidator = new ResourceValidator($this->resourceMetadataFactory, $this->validatorMock);
        $this->resourceFactory = new ResourceFactory(
            $this->resourceMetadataFactory,
            $this->shouldValidateReadModel,
            $this->resourceValidator,
        );
    }

    public function testResourceFactoryCreatesValidResourceWithoutAttributesOrRelationshipsValues(): void
    {
        $resourceDto = $this->createMinimalResource();

        $resource = $this->resourceFactory->make($resourceDto);
        $this->assertFullResourceGiven($resource);
    }

    public function testResourceFactoryCreatesValidResourceWithValues(): void
    {
        $resourceDto = $this->createFullResource();

        $resource = $this->resourceFactory->make($resourceDto);
        $this->assertFullResourceGiven($resource);

        $flatResource = new FlatResource($resource);
        self::assertSame('Resource title', $flatResource->getAttributes()['title']);
        self::assertSame('Resource summary', $flatResource->getAttributes()['summary']);
        self::assertSame('2018-01-01', $flatResource->getAttributes()['date']);
        self::assertSame(['t1', 't2', 't3'], $flatResource->getRelationships()['tags']);
        self::assertSame(['c1', 'c2', 'c3'], $flatResource->getRelationships()['comments']);
        self::assertSame('a1', $flatResource->getRelationships()['author']);
    }

    public function testResourceFactoryCreatesValidTagsRelationship(): void
    {
        $resourceDto = new ResourceDto(
            '1',
            'Resource title',
            null,
            null,
            null,
            ['t1', 't2', 't3'],
            [],
        );

        $resource = $this->resourceFactory->make($resourceDto);
        $this->assertMinimumResourceParametersAreValid($resource);

        $tagsRelationship = $resource->getRelationships()->getRelationshipByName('tags');
        self::assertInstanceOf(RelationshipInterface::class, $tagsRelationship);
        self::assertInstanceOf(ToManyRelationshipDataInterface::class, $tagsRelationship->getData());

        self::assertEquals(
            new ResourceIdentifier('t1', 'tags'),
            $tagsRelationship->getData()->getData()->getResourceIdentifiers()[0]
        );

        self::assertEquals(
            new ResourceIdentifier('t2', 'tags'),
            $tagsRelationship->getData()->getData()->getResourceIdentifiers()[1]
        );

        self::assertEquals(
            new ResourceIdentifier('t3', 'tags'),
            $tagsRelationship->getData()->getData()->getResourceIdentifiers()[2]
        );
    }

    public function testResourceFactoryCreatesValidEmptyTagsRelationshipFromEmptyArray(): void
    {
        $resourceDto = $this->createMinimalResource();

        $resource = $this->resourceFactory->make($resourceDto);
        $this->assertMinimumResourceParametersAreValid($resource);

        $tagsRelationship = $resource->getRelationships()->getRelationshipByName('tags');
        self::assertInstanceOf(RelationshipInterface::class, $tagsRelationship);
        self::assertInstanceOf(ToManyRelationshipDataInterface::class, $tagsRelationship->getData());
        self::assertTrue($tagsRelationship->getData()->isEmpty());
    }

    public function testResourceFactoryCreatesValidAuthorRelationship(): void
    {
        $resourceDto = new ResourceDto(
            '1',
            'Resource title',
            null,
            null,
            'a1',
            [],
            [],
        );

        $resource = $this->resourceFactory->make($resourceDto);
        $this->assertMinimumResourceParametersAreValid($resource);

        $authorRelationship = $resource->getRelationships()->getRelationshipByName('author');
        self::assertInstanceOf(RelationshipInterface::class, $authorRelationship);
        self::assertInstanceOf(ToOneRelationshipDataInterface::class, $authorRelationship->getData());

        self::assertEquals(
            new ResourceIdentifier('a1', 'people'),
            $authorRelationship->getData()->getData()
        );
    }

    public function testMakeWillCreateValidResourceGivenWriteModelShouldNotBeValidatedAndResourceHasValues(): void
    {
        $resourceDto = $this->createFullResource();

        $this->shouldValidateReadModel = false;
        $resource = $this->resourceFactory->make($resourceDto);
        $this->assertFullResourceGiven($resource);

        $flatResource = new FlatResource($resource);
        self::assertSame('Resource title', $flatResource->getAttributes()['title']);
        self::assertSame('Resource summary', $flatResource->getAttributes()['summary']);
        self::assertSame('2018-01-01', $flatResource->getAttributes()['date']);
        self::assertSame(['t1', 't2', 't3'], $flatResource->getRelationships()['tags']);
        self::assertSame(['c1', 'c2', 'c3'], $flatResource->getRelationships()['comments']);
        self::assertSame('a1', $flatResource->getRelationships()['author']);
    }

    public function testMakeWillCreateValidResourceGivenWriteModelShouldBeValidatedAndResourceHasValuesAndConstraints(): void
    {
        $resourceDto = $this->createFullResource();

        $this->shouldValidateReadModel = true;
        $resourceFactory = new ResourceFactory(
            $this->resourceMetadataFactory,
            $this->shouldValidateReadModel,
            $this->resourceValidator,
        );
        $resource = $resourceFactory->make($resourceDto);
        $this->assertFullResourceGiven($resource);

        $flatResource = new FlatResource($resource);
        self::assertSame('Resource title', $flatResource->getAttributes()['title']);
        self::assertSame('Resource summary', $flatResource->getAttributes()['summary']);
        self::assertSame('2018-01-01', $flatResource->getAttributes()['date']);
        self::assertSame(['t1', 't2', 't3'], $flatResource->getRelationships()['tags']);
        self::assertSame(['c1', 'c2', 'c3'], $flatResource->getRelationships()['comments']);
        self::assertSame('a1', $flatResource->getRelationships()['author']);
    }

    public function testMakeWillThrowExceptionGivenWriteModelShouldBeValidatedAndResourceHasValuesWhichAreAgainstConstraints(): void
    {
        $this->expectException(\Exception::class);
        $validatorMock = $this->createMock(ValidatorInterface::class);
        $validatorMock
            ->expects(self::exactly(4))
            ->method('validate')
            ->willReturn(new ResourceValidationViolations(
                new ConstraintViolationList(
                    [new ConstraintViolation('Test', null, [], null, null, null)],
                ),
                new ConstraintViolationList(),
                new ConstraintViolationList(),
            ));
        $resourceValidator = new ResourceValidator($this->resourceMetadataFactory, $validatorMock);
        $this->shouldValidateReadModel = true;

        $resourceDto = new ResourceDto(
            '1',
            '',
            null,
            null,
            null,
            [],
            [],
        );

        $resourceFactory = new ResourceFactory(
            $this->resourceMetadataFactory,
            $this->shouldValidateReadModel,
            $resourceValidator,
        );
        $resourceFactory->make($resourceDto);
    }

    private function assertMinimumResourceParametersAreValid(ResourceInterface $resource): void
    {
        self::assertSame('1', $resource->getId());
        self::assertSame('resource', $resource->getType());
    }

    private function assertFullResourceGiven(ResourceInterface $resource): void
    {
        $this->assertMinimumResourceParametersAreValid($resource);
        self::assertCount(3, $resource->getAttributes());
        self::assertCount(3, $resource->getRelationships());
    }

    private function createMinimalResource(): ResourceDto
    {
        return new ResourceDto(
            '1',
            'Resource title',
            null,
            null,
            'a1',
            [],
            [],
        );
    }

    private function createFullResource(): ResourceDto
    {
        return new ResourceDto(
            '1',
            'Resource title',
            '2018-01-01',
            'Resource summary',
            'a1',
            ['t1', 't2', 't3'],
            ['c1', 'c2', 'c3'],
        );
    }
}

/**
 * @ResourceType(type="resource")
 */
final class ResourceDto implements ApiModel
{
    /** @Assert\NotBlank */
    public string $id;

    /**
     * @JsonApi\Attribute
     *
     * @Assert\NotBlank
     */
    public string $title;

    /** @JsonApi\Attribute */
    public ?string $date;

    /** @JsonApi\Attribute */
    public ?string $summary;

    /** @JsonApi\ToOne(type="people") */
    public ?string $author;

    /**
     * @var array<int,string>
     *
     * @JsonApi\ToMany(type="tags")
     *
     * @Assert\All({
     *
     *     @Assert\NotBlank,
     *
     *     @Assert\Type("string")
     * })
     */
    public array $tags = [];

    /**
     * @var array<int,string>
     *
     * @JsonApi\ToMany(type="comments")
     *
     * @Assert\All({
     *
     *     @Assert\NotBlank,
     *
     *     @Assert\Type("string")
     * })
     */
    public array $comments = [];

    /**
     * @param array<int,string> $tags
     * @param array<int,string> $comments
     */
    public function __construct(
        string $id,
        ?string $title,
        ?string $date,
        ?string $summary,
        ?string $author,
        array $tags,
        array $comments
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->date = $date;
        $this->summary = $summary;
        $this->author = $author;
        $this->tags = $tags;
        $this->comments = $comments;
    }
}
