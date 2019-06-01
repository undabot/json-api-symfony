<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Http\Response;

use Symfony\Component\HttpFoundation\Response;
use Undabot\JsonApi\Model\Resource\ResourceInterface;

class ResourceUpdatedJsonApiResponse extends Response implements JsonApiResponseInterface
{
    /** @var ResourceInterface */
    private $jsonApiResource;

    public function __construct(ResourceInterface $resource, array $headers = [])
    {
        parent::__construct(null, Response::HTTP_OK, $headers);
        $this->jsonApiResource = $resource;
    }

    public function getJsonApiResource(): ResourceInterface
    {
        return $this->jsonApiResource;
    }
}
