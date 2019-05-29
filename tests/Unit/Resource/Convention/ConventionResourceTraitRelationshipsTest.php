<?php

declare(strict_types=1);

namespace Undabot\JsonApi\Tests\Unit\Resource\Convention;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Undabot\JsonApi\Model\Resource\Relationship\Data\ToManyRelationshipData;
use Undabot\JsonApi\Model\Resource\Relationship\Data\ToOneRelationshipData;
use Undabot\JsonApi\Model\Resource\Relationship\RelationshipCollection;
use Undabot\JsonApi\Model\Resource\ResourceIdentifierInterface;
use Undabot\SymfonyJsonApi\Resource\Model\AnnotatedResource\AnnotatedResourceTrait;
use Undabot\SymfonyJsonApi\Resource\Model\ConventionResourceTrait;

class ConventionResourceTraitRelationshipsTest extends TestCase
{
    public function testAnnotatedResourceReturnsAnnotatedAttributes()
    {
        $resource = new class
        {
            use ConventionResourceTrait;

            public $tagIds;
            public $ownerId;
            public $emptyToOneId;
            public $emptyToManyIds;
            public $notARelationship;

            public function __construct()
            {
                $this->ignoredProperties[] = 'notARelationship';
            }

            protected function modifyRelationshipName(string $name): string
            {
                $map = [
                    'tag' => 'tags',
                ];

                return $map[$name] ?? $name;
            }
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
        $this->assertSame('owner', $ownerData->getData()->getType());
        $this->assertSame('owner1', $ownerData->getData()->getId());

        $this->assertNull($relationships->getRelationshipByName('notARelationship'));
    }

    public function testAnnotatedResourceReturnsEmptyCollectionWhenNoRelationshipsAreAnnotated()
    {
        $resource = new class
        {
            use ConventionResourceTrait;
        };

        $this->assertEmpty($resource->getRelationships()->getRelationships());
    }
}
