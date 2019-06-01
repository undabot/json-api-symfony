<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Http\Model\Response;

use Symfony\Component\HttpFoundation\Response;
use Undabot\JsonApi\Model\Resource\ResourceInterface;

class ResourceCreatedJsonApiResponse extends Response implements JsonApiResponseInterface
{
    /** @var ResourceInterface */
    private $jsonApiResource;

    public function __construct(ResourceInterface $resource, array $headers = [])
    {
        parent::__construct(null, Response::HTTP_CREATED, $headers);
        $this->jsonApiResource = $resource;
    }

    public function getJsonApiResource(): ResourceInterface
    {
        return $this->jsonApiResource;
    }
}
