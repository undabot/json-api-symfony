<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Http\Service\Responder;

use Exception;
use Undabot\JsonApi\Model\Resource\ResourceCollection;
use Undabot\SymfonyJsonApi\Http\Model\Response\ResourceCollectionResponse;
use Undabot\SymfonyJsonApi\Http\Model\Response\ResourceCreatedResponse;
use Undabot\SymfonyJsonApi\Http\Model\Response\ResourceResponse;
use Undabot\SymfonyJsonApi\Http\Model\Response\ResourceUpdatedResponse;

final class ModelResponder extends AbstractResponder
{
    /**
     * @throws Exception
     */
    public function resourceCollectionResponse(
        array $primaryModels,
        array $includedModels = null,
        array $meta = null,
        array $links = null
    ): ResourceCollectionResponse {
        $primaryResources = $this->encoder->encodeModels($primaryModels);

        return new ResourceCollectionResponse(
            new ResourceCollection($primaryResources),
            $this->buildIncluded($includedModels),
            $this->buildMeta($meta),
            $this->buildLinks($links)
        );
    }

    /**
     * @throws Exception
     */
    public function resourceResponse(
        $model,
        array $includedModels = null,
        array $meta = null,
        array $links = null
    ): ResourceResponse {
        $resource = $this->encoder->encodeModel($model);

        return new ResourceResponse(
            $resource,
            $this->buildIncluded($includedModels),
            $this->buildMeta($meta),
            $this->buildLinks($links)
        );
    }

    /**
     * @throws Exception
     */
    public function resourceCreatedResponse(
        $model,
        array $includedModels = null,
        array $meta = null,
        array $links = null
    ): ResourceCreatedResponse {
        $resource = $this->encoder->encodeModel($model);

        return new ResourceCreatedResponse(
            $resource,
            $this->buildIncluded($includedModels),
            $this->buildMeta($meta),
            $this->buildLinks($links)
        );
    }

    /**
     * @throws Exception
     */
    public function resourceUpdatedResponse(
        $model,
        array $includedModels = null,
        array $meta = null,
        array $links = null
    ): ResourceUpdatedResponse {
        $resource = $this->encoder->encodeModel($model);

        return new ResourceUpdatedResponse(
            $resource,
            $this->buildIncluded($includedModels),
            $this->buildMeta($meta),
            $this->buildLinks($links)
        );
    }
}
