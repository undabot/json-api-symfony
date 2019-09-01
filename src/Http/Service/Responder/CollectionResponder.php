<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Http\Service\Responder;

use Exception;
use Undabot\JsonApi\Model\Meta\Meta;
use Undabot\JsonApi\Model\Resource\ResourceCollection;
use Undabot\SymfonyJsonApi\Http\Model\Response\ResourceCollectionResponse;
use Undabot\SymfonyJsonApi\Model\Collection\ObjectCollection;

/**
 * Opinionated collection responder that adds meta information about total count of models, used for pagination.
 */
final class CollectionResponder extends AbstractResponder
{
    /**
     * @throws Exception
     */
    public function resourceCollection(
        ObjectCollection $primaryModels,
        array $included = null,
        array $meta = null,
        array $links = null
    ): ResourceCollectionResponse {
        $primaryResources = $this->encoder->encodeModels($primaryModels->getItems());
        $meta = $meta ?? ['total' => $primaryModels->count()];

        return new ResourceCollectionResponse(
            new ResourceCollection($primaryResources),
            null === $included ? null : $this->buildIncluded($included),
            null === $meta ? null : new Meta($meta),
            null === $links ? null : $this->buildLinks($links)
        );
    }
}
