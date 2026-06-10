<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Tests\Integration\Resource\Validation;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Undabot\JsonApi\Implementation\Model\Resource\Resource;
use Undabot\SymfonyJsonApi\Model\Resource\Annotation as JsonApi;
use Undabot\SymfonyJsonApi\Service\Resource\Builder\ResourceRelationshipsBuilder;
use Undabot\SymfonyJsonApi\Service\Resource\Factory\ResourceMetadataFactory;
use Undabot\SymfonyJsonApi\Service\Resource\Validation\Constraint\ResourceType;
use Undabot\SymfonyJsonApi\Service\Resource\Validation\Constraint\ToMany;
use Undabot\SymfonyJsonApi\Service\Resource\Validation\Constraint\ToOne;
use Undabot\SymfonyJsonApi\Service\Resource\Validation\ResourceValidator;

/**
 * @internal
 */
#[CoversNothing]
#[Small]
final class ResourceRelationshipsValidationTest extends TestCase
{
    /** @var ResourceValidator */
    private $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        $metadataFactory = new ResourceMetadataFactory();

        $this->validator = new ResourceValidator($metadataFactory, $validator);
    }

    public function testValidatorRecognizesSingleIdAsInvalidToManyRelationshipValue(): void
    {
        $resourceDto
                        = new #[ResourceType(type: 'resource')] class() {
                            /**
                             * @var string[]
                             */
                            #[JsonApi\ToMany(type: 'type')]
                            public $relationship = [];
                        };

        $resource = new Resource(
            '1',
            'resource',
            null,
            ResourceRelationshipsBuilder::make()
                ->toOne('relationship', 'type', '1')
                ->get()
        );

        $violations = $this->validator->validate($resource, $resourceDto::class);
        self::assertSame(1, $violations->count());
        self::assertSame($violations[0]->getMessageTemplate(), ToMany::MESSAGE);
    }

    public function testValidatorRecognizesNullAsInvalidToManyRelationshipValue(): void
    {
        $resourceDto
                        = new #[ResourceType(type: 'resource')] class() {
                            /**
                             * @var string[]
                             */
                            #[JsonApi\ToMany(type: 'type')]
                            public $relationship = [];
                        };

        $resource = new Resource(
            '1',
            'resource',
            null,
            ResourceRelationshipsBuilder::make()
                ->toOne('relationship', 'type', null)
                ->get()
        );

        $violations = $this->validator->validate($resource, $resourceDto::class);
        self::assertSame(1, $violations->count());
        self::assertSame($violations[0]->getMessageTemplate(), ToMany::MESSAGE);
    }

    public function testValidatorRecognizesArrayAsInvalidToOneRelationshipValue(): void
    {
        $resourceDto
                        = new #[ResourceType(type: 'resource')] class() {
                            /**
                             * @var string[]
                             */
                            #[JsonApi\ToOne(type: 'type')]
                            public $relationship;
                        };

        $resource = new Resource(
            '1',
            'resource',
            null,
            ResourceRelationshipsBuilder::make()
                ->toMany('relationship', 'type', ['1', '2'])
                ->get()
        );

        $violations = $this->validator->validate($resource, $resourceDto::class);
        self::assertSame(1, $violations->count());
        self::assertSame($violations[0]->getMessageTemplate(), ToOne::MESSAGE);
    }
}
