<?php

declare(strict_types=1);

namespace Undabot\JsonApi\Tests\Unit\Resource\Annotated;

use DateTimeImmutable;
use LogicException;
use PHPUnit\Framework\TestCase;
use Undabot\JsonApi\Model\Resource\Relationship\Data\ToManyRelationshipData;
use Undabot\JsonApi\Model\Resource\Relationship\Data\ToOneRelationshipData;
use Undabot\JsonApi\Model\Resource\Relationship\RelationshipCollection;
use Undabot\JsonApi\Model\Resource\ResourceIdentifierInterface;
use Undabot\SymfonyJsonApi\Resource\Model\AnnotatedResource\AnnotatedResourceTrait;
use Undabot\SymfonyJsonApi\Resource\Model\AnnotatedResource\Annotation as JsonApi;

class AnnotatedResourceTraitRelationshipTest extends TestCase
{
    public function testAnnotatedResourceReturnsAnnotatedAttributes()
    {
        $resource = new class
        {
            use AnnotatedResourceTrait;

            /**
             * @var array
             * @JsonApi\ToMany(name="tags", type="tag")
             */
            public $tagIds;

            /**
             * @var string|null
             * @JsonApi\ToOne(name="owner", type="person")
             */
            public $ownerId;

            /**
             * @JsonApi\ToOne(name="emptyOne", type="emptyOneType")
             */
            public $emptyToOne;

            /**
             * @JsonApi\ToMany(name="emptyMany", type="emptyManyType")
             */
            public $emptyToMany;

            public $notARelationship;
        };

        $resource->tagIds = ['tag1', 'tag2', 'tag3'];
        $resource->ownerId = 'owner1';
        $resource->notARelationship = 'x';

        /** @var RelationshipCollection $relationships */
        $relationships = $resource->getRelationships();

        $this->assertCount(4, $relationships);

        /** @var ToManyRelationshipData $tagsData */
        $tagsData = $relationships->getRelationshipByName('tags')->getData();
        $this->assertFalse($tagsData->isEmpty());
        $this->assertInstanceOf(ToManyRelationshipData::class, $tagsData);

        $tagIds = [];
        /** @var ResourceIdentifierInterface $resourceIdentifier */
        foreach ($tagsData->getData()->getResourceIdentifiers() as $resourceIdentifier) {
            $this->assertSame('tag', $resourceIdentifier->getType());
            $tagIds[] = $resourceIdentifier->getId();
        }
        $this->assertSame(['tag1', 'tag2', 'tag3'], $tagIds);

        /** @var ToOneRelationshipData $ownerData */
        $ownerData = $relationships->getRelationshipByName('owner')->getData();
        $this->assertFalse($ownerData->isEmpty());
        $this->assertInstanceOf(ToOneRelationshipData::class, $ownerData);
        $this->assertSame('person', $ownerData->getData()->getType());
        $this->assertSame('owner1', $ownerData->getData()->getId());

        $this->assertNull($relationships->getRelationshipByName('notARelationship'));
    }

    public function testAnnotatedResourceThrowsAnExceptionWhenSinglePropertyHasMultipleRelationshipAnnotations()
    {
        $resource = new class
        {
            use AnnotatedResourceTrait;

            /**
             * @var array
             * @JsonApi\ToMany(name="tags", type="tag")
             * @JsonApi\ToOne(name="owner", type="person")
             */
            public $tagIds;
        };

        $this->expectException(LogicException::class);
        $resource->getRelationships();
    }

    public function testAnnotatedResourceReturnsEmptyCollectionWhenNoRelationshipsAreAnnotated()
    {
        $resource = new class
        {
            use AnnotatedResourceTrait;
        };

        $this->assertEmpty($resource->getRelationships()->getRelationships());
    }
}
