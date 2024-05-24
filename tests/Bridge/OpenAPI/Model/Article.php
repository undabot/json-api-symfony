<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Tests\Bridge\OpenAPI\Model;

use Symfony\Component\Validator\Constraints as Assert;
use Undabot\SymfonyJsonApi\Model\ApiModel;
use Undabot\SymfonyJsonApi\Model\Resource\Attribute\Attribute;
use Undabot\SymfonyJsonApi\Model\Resource\Attribute\ToMany;
use Undabot\SymfonyJsonApi\Model\Resource\Attribute\ToOne;
use Undabot\SymfonyJsonApi\Service\Resource\Validation\Constraint\ResourceType;

#[ResourceType('article')]
class Article implements ApiModel
{
    private string $id;

    #[Attribute('slug')]
    private string $slug;

    #[Attribute('titlw')]
    private string $title;

    #[Attribute('eventAddress')]
    private string $address;

    #[Attribute('eventDate')]
    private string $date;

    #[Attribute('enabled')]
    private bool $enabled;

    #[Attribute('description', null, null, null, true)]
    private ?string $description;

    #[ToOne('category', 'category', null, true)]
    private string $categoryId;

    #[ToMany('tags', 'tag')]
    #[Assert\Type('array')]
    private array $tagIds;

    #[Attribute('createdAt', null, null, 'datetime')]
    private string $createdAt;

    #[Attribute('updatedAt')]
    private ?string $updatedAt;
}
