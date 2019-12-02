<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Http\Service\Responder;

use Exception;
use Undabot\JsonApi\Implementation\Model\Meta\Meta;
use Undabot\JsonApi\Implementation\Model\Resource\ResourceCollection;
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
        $primaryResources = $this->encoder->encodeDataset($primaryModels->getItems());
        $meta = $meta ?? ['total' => $primaryModels->count()];

        return new ResourceCollectionResponse(
            new ResourceCollection($primaryResources),
            null === $included ? null : $this->buildIncluded($included),
            new Meta($meta),
            null === $links ? null : $this->buildLinks($links)
        );
    }
}
