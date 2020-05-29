<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Tests\Integration\Resource\Validation;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
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
 * @coversNothing
 *
 * @small
 */
final class ResourceRelationshipsValidationTest extends KernelTestCase
{
    /** @var ResourceValidator */
    private $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $validator = Validation::createValidatorBuilder()
            ->enableAnnotationMapping()
            ->getValidator();

        AnnotationRegistry::registerLoader('class_exists');
        $annotationReader = new AnnotationReader();
        $metadataFactory = new ResourceMetadataFactory($annotationReader);

        $this->validator = new ResourceValidator($metadataFactory, $validator);
    }

    public function testValidatorRecognizesSingleIdAsInvalidToManyRelationshipValue(): void
    {
        $resourceDto =
            /**
             * @ResourceType(type="resource")
             */
            new class() {
                /**
                 * @var string[]
                 * @JsonApi\ToMany(type="type")
                 */
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

        $violations = $this->validator->validate($resource, \get_class($resourceDto));
        static::assertSame(1, $violations->count());
        static::assertSame($violations[0]->getMessageTemplate(), ToMany::MESSAGE);
    }

    public function testValidatorRecognizesNullAsInvalidToManyRelationshipValue(): void
    {
        $resourceDto =
            /**
             * @ResourceType(type="resource")
             */
            new class() {
                /**
                 * @var string[]
                 * @JsonApi\ToMany(type="type")
                 */
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

        $violations = $this->validator->validate($resource, \get_class($resourceDto));
        static::assertSame(1, $violations->count());
        static::assertSame($violations[0]->getMessageTemplate(), ToMany::MESSAGE);
    }

    public function testValidatorRecognizesArrayAsInvalidToOneRelationshipValue(): void
    {
        $resourceDto =
            /**
             * @ResourceType(type="resource")
             */
            new class() {
                /**
                 * @var string[]
                 * @JsonApi\ToOne(type="type")
                 */
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

        $violations = $this->validator->validate($resource, \get_class($resourceDto));
        static::assertSame(1, $violations->count());
        static::assertSame($violations[0]->getMessageTemplate(), ToOne::MESSAGE);
    }
}
