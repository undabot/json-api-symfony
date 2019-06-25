<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Http\Service\Responder;

use Exception;
use Undabot\JsonApi\Model\Resource\ResourceCollection;
use Undabot\SymfonyJsonApi\Http\Model\Response\ResourceCollectionResponse;
use Undabot\SymfonyJsonApi\Model\Collection\ObjectCollectionInterface;

final class ObjectCollectionResponder extends AbstractResponder
{
    /**
     * @throws Exception
     */
    public function resourceCollectionResponse(
        ObjectCollectionInterface $primaryModels,
        array $includedModels = null,
        array $meta = null,
        array $links = null
    ): ResourceCollectionResponse {
        $primaryResources = $this->encoder->encodeModels($primaryModels->getItems());
        $meta = $meta ?? ['total' => $primaryModels->count()];

        return new ResourceCollectionResponse(
            new ResourceCollection($primaryResources),
            $this->buildIncluded($includedModels),
            $this->buildMeta($meta),
            $this->buildLinks($links)
        );
    }
}
