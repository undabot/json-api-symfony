<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Tests\Bridge\OpenAPI\Model;

use Symfony\Component\Validator\Constraints as Assert;
use Undabot\SymfonyJsonApi\Model\ApiModel;
use Undabot\SymfonyJsonApi\Model\Resource\Annotation\Attribute;
use Undabot\SymfonyJsonApi\Model\Resource\Annotation\ToMany;
use Undabot\SymfonyJsonApi\Model\Resource\Annotation\ToOne;
use Undabot\SymfonyJsonApi\Service\Resource\Validation\Constraint\ResourceType;

/** @ResourceType(type="article") */
class Article implements ApiModel
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     * @Attribute(nullable=false)
     */
    private $slug;

    /**
     * @var string
     * @Attribute
     */
    private $title;

    /**
     * @var string
     * @Attribute(name="eventAddress")
     */
    private $address;

    /**
     * @var string
     * @Attribute(name="eventDate")
     */
    private $date;

    /**
     * @var bool
     * @Attribute
     */
    private $enabled;

    /**
     * @var null|string
     * @Attribute
     */
    private $description;

    /**
     * @var string
     * @ToOne(name="category", type="category", nullable=true)
     * @Assert\Type(type="string")
     */
    private $categoryId;

    /**
     * @var string[]
     * @ToMany(name="tags", type="tag", nullable=false)
     * @Assert\Type(type="array")
     */
    private $tagIds;

    /**
     * @var string
     * @Attribute(format="datetime", example="2001")
     * @Assert\NotBlank
     */
    private $createdAt;

    /**
     * @var null|string
     * @Attribute
     */
    private $updatedAt;
}
