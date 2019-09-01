<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Http\Service\Responder;

use Exception;
use Undabot\JsonApi\Model\Meta\Meta;
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
    public function resourceCollection(
        array $primaryModels,
        array $included = null,
        array $meta = null,
        array $links = null
    ): ResourceCollectionResponse {
        $primaryResources = $this->encoder->encodeModels($primaryModels);

        return new ResourceCollectionResponse(
            new ResourceCollection($primaryResources),
            null === $included ? null : $this->buildIncluded($included),
            null === $meta ? null : new Meta($meta),
            null === $links ? null : $this->buildLinks($links)
        );
    }

    /**
     * @throws Exception
     */
    public function resource(
        $data,
        array $included = null,
        array $meta = null,
        array $links = null
    ): ResourceResponse {
        $resource = $this->encoder->encodeModel($data);

        return new ResourceResponse(
            $resource,
            null === $included ? null : $this->buildIncluded($included),
            null === $meta ? null : new Meta($meta),
            null === $links ? null : $this->buildLinks($links)
        );
    }

    /**
     * @throws Exception
     */
    public function resourceCreated(
        $model,
        array $included = null,
        array $meta = null,
        array $links = null
    ): ResourceCreatedResponse {
        $resource = $this->encoder->encodeModel($model);

        return new ResourceCreatedResponse(
            $resource,
            null === $included ? null : $this->buildIncluded($included),
            null === $meta ? null : new Meta($meta),
            null === $links ? null : $this->buildLinks($links)
        );
    }

    /**
     * @throws Exception
     */
    public function resourceUpdated(
        $data,
        array $included = null,
        array $meta = null,
        array $links = null
    ): ResourceUpdatedResponse {
        $resource = $this->encoder->encodeModel($data);

        return new ResourceUpdatedResponse(
            $resource,
            null === $included ? null : $this->buildIncluded($included),
            null === $meta ? null : new Meta($meta),
            null === $links ? null : $this->buildLinks($links)
        );
    }
}
