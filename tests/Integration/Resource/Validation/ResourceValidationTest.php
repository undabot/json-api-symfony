<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Tests\Integration\Resource\Validation;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;
use Undabot\JsonApi\Model\Resource\Resource;
use Undabot\SymfonyJsonApi\Model\Attribute\ResourceAttributesFactory;
use Undabot\SymfonyJsonApi\Model\Relationship\ResourceRelationshipsFactory;
use Undabot\SymfonyJsonApi\Resource\Model\AnnotatedResource\Annotation as JsonApi;
use Undabot\SymfonyJsonApi\Resource\Model\Metadata\Factory\ResourceMetadataFactory;
use Undabot\SymfonyJsonApi\Resource\Validation\Constraint\ResourceType;
use Undabot\SymfonyJsonApi\Resource\Validation\ResourceValidator;

/**
 * @ResourceType(type="articles")
 */
class ResourceDto
{
    /** @var string */
    public $id;

    /**
     * @var string
     * @JsonApi\Attribute()
     * @Assert\NotBlank()
     * @Assert\Length(min=10, max=100)
     * @Assert\Type(type="string")
     */
    public $title;

    /**
     * @var string
     * @JsonApi\Attribute()
     * @Assert\Type(type="string")
     * @Assert\Regex(pattern="/^\d+-\d+-\d+$/")
     */
    public $date;

    /**
     * @var string
     * @JsonApi\Attribute()
     * @Assert\Type(type="string")
     * @Assert\NotNull()
     */
    public $summary;

    /**
     * @var string|null
     * @JsonApi\ToOne(type="people")
     * @Assert\NotBlank()
     */
    public $author;

    /**
     * @var string[]
     * @JsonApi\ToMany(type="tags")
     * @Assert\NotBlank()
     */
    public $tags = [];

    /**
     * @var string[]
     * @JsonApi\ToMany(type="comments")
     */
    public $comments = [];
}

class ResourceValidationTest extends KernelTestCase
{
    /** @var ResourceValidator */
    private $validator;

    protected function setUp()
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

    private function buildResource(array $data, $id = '1', $type = 'articles')
    {
        return new Resource(
            $id,
            $type,
            ResourceAttributesFactory::make()
                ->add('title', $data['title'])
                ->add('date', $data['date'])
                ->add('summary', $data['summary'])
                ->get(),
            ResourceRelationshipsFactory::make()
                ->toOne('author', 'people', $data['author'])
                ->toMany('comments', 'comments', $data['comments'])
                ->toMany('tags', 'tags', $data['tags'])
                ->get()
        );
    }

    public function testValidatorRecognizeThatValidResourceHasNoViolations()
    {
        $resource = $this->buildResource([
            'title' => 'JSON:API paints my bikeshed!',
            'date' => '2019-04-13',
            'summary' => 'This is my summary',
            'author' => '9',
            'comments' => ['5', '12'],
            'tags' => ['t1', 't2'],
        ]);

        $violations = $this->validator->validate($resource, ResourceDto::class);
        $this->assertSame(0, $violations->count());
    }

    public function testValidatorRecognizesMissingAttributesAndRelationshipsAsInvalid()
    {
        $resource = new Resource('1', 'articles');

        $violations = $this->validator->validate($resource, ResourceDto::class);
        $this->assertSame(6, $violations->count());
    }

    public function testValidatorRecognizesEmptyTitleStringAsInvalid()
    {
        $resource = $this->buildResource([
            'title' => '',
            'date' => '2019-04-13',
            'summary' => 'This is my summary',
            'author' => '9',
            'comments' => ['5', '12'],
            'tags' => ['t1', 't2'],
        ]);

        $violations = $this->validator->validate($resource, ResourceDto::class);
        $this->assertSame(1, $violations->count());

        $this->assertSame('This value should not be blank.', $violations[0]->getMessage());
        $this->assertSame('[data][attributes][title]', $violations[0]->getPropertyPath());
    }

    public function testValidatorRecognizesNullTitleAsInvalid()
    {
        $resource = $this->buildResource([
            'title' => null,
            'date' => '2019-04-13',
            'summary' => 'This is my summary',
            'author' => '9',
            'comments' => ['5', '12'],
            'tags' => ['t1', 't2'],
        ]);

        $violations = $this->validator->validate($resource, ResourceDto::class);
        $this->assertSame(1, $violations->count());

        $this->assertSame('This value should not be blank.', $violations[0]->getMessage());
        $this->assertSame('[data][attributes][title]', $violations[0]->getPropertyPath());
    }

    public function testValidatorRecognizesTooShortTitleAsInvalid()
    {
        $resource = $this->buildResource([
            'title' => 'short',
            'date' => '2019-04-13',
            'summary' => 'This is my summary',
            'author' => '9',
            'comments' => ['5', '12'],
            'tags' => ['t1', 't2'],
        ]);

        $violations = $this->validator->validate($resource, ResourceDto::class);
        $this->assertSame(1, $violations->count());

        $this->assertSame(
            'This value is too short. It should have 10 characters or more.',
            $violations[0]->getMessage()
        );
        $this->assertSame('[data][attributes][title]', $violations[0]->getPropertyPath());
    }

    public function testValidatorRecognizesMissingAuthorIdAsInvalid()
    {
        $resource = $this->buildResource([
            'title' => 'JSON:API paints my bikeshed!',
            'date' => '2019-04-13',
            'summary' => 'This is my summary',
            'author' => null,
            'comments' => ['5', '12'],
            'tags' => ['t1', 't2'],
        ]);

        $violations = $this->validator->validate($resource, ResourceDto::class);
        $this->assertSame(1, $violations->count());

        $this->assertSame(
            'This value should not be blank.',
            $violations[0]->getMessage()
        );
        $this->assertSame('[data][relationships][author]', $violations[0]->getPropertyPath());
    }

    public function testValidatorRecognizesEmptyArrayAsValidValueForOptionalToManyRelationshipComments()
    {
        $resource = $this->buildResource([
            'title' => 'JSON:API paints my bikeshed!',
            'date' => '2019-04-13',
            'summary' => 'This is my summary',
            'author' => '9',
            'comments' => [],
            'tags' => ['t1', 't2'],
        ]);

        $violations = $this->validator->validate($resource, ResourceDto::class);
        $this->assertSame(0, $violations->count());
    }

    public function testValidatorRecognizesEmptyArrayAsInvalidValueForNonOptionalToManyRelationshipTags()
    {
        $resource = $this->buildResource([
            'title' => 'JSON:API paints my bikeshed!',
            'date' => '2019-04-13',
            'summary' => 'This is my summary',
            'author' => '9',
            'comments' => [],
            'tags' => [],
        ]);

        $violations = $this->validator->validate($resource, ResourceDto::class);
        $this->assertSame(1, $violations->count());
        $this->assertSame(
            'This value should not be blank.',
            $violations[0]->getMessage()
        );
        $this->assertSame('[data][relationships][tags]', $violations[0]->getPropertyPath());
    }

    public function testValidatorRecognizesNullAsValidValueForOptionalAttributeDate()
    {
        $resource = $this->buildResource([
            'title' => 'JSON:API paints my bikeshed!',
            'date' => null,
            'summary' => 'This is my summary',
            'author' => '9',
            'comments' => [],
            'tags' => ['t1', 't2'],
        ]);

        $violations = $this->validator->validate($resource, ResourceDto::class);
        $this->assertSame(0, $violations->count());
    }

    public function testValidatorRecognizesInvalidDateString()
    {
        $resource = $this->buildResource([
            'title' => 'JSON:API paints my bikeshed!',
            'date' => 'xxx',
            'summary' => 'This is my summary',
            'author' => '9',
            'comments' => [],
            'tags' => ['t1', 't2'],
        ]);

        $violations = $this->validator->validate($resource, ResourceDto::class);
        $this->assertSame(1, $violations->count());
        $this->assertSame(
            'This value is not valid.',
            $violations[0]->getMessage()
        );
        $this->assertSame('[data][attributes][date]', $violations[0]->getPropertyPath());
    }

    public function testValidatorRecognizeInvalidResourceTypeAsInvalid()
    {
        $resource = $this->buildResource([
            'title' => 'JSON:API paints my bikeshed!',
            'date' => '2019-04-13',
            'summary' => 'This is my summary',
            'author' => '9',
            'comments' => ['5', '12'],
            'tags' => ['t1', 't2'],
        ], '1', 'invalidTypes');

        $violations = $this->validator->validate($resource, ResourceDto::class);
        $this->assertSame(1, $violations->count());

        $this->assertSame(
            'Invalid resource type invalidTypes given; articles expected.',
            $violations[0]->getMessage()
        );
        $this->assertSame('', $violations[0]->getPropertyPath());
    }
}
