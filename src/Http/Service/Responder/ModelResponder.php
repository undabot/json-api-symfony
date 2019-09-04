<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Http\Service\Responder;

use Exception;
use Undabot\JsonApi\Model\Link\LinkMemberInterface;
use Undabot\JsonApi\Model\Meta\Meta;
use Undabot\JsonApi\Model\Resource\ResourceCollection;
use Undabot\SymfonyJsonApi\Http\Model\Response\ResourceCollectionResponse;
use Undabot\SymfonyJsonApi\Http\Model\Response\ResourceCreatedResponse;
use Undabot\SymfonyJsonApi\Http\Model\Response\ResourceResponse;
use Undabot\SymfonyJsonApi\Http\Model\Response\ResourceUpdatedResponse;

final class ModelResponder extends AbstractResponder
{
    /**
     * @param mixed[]                                 $primaryData
     * @param null|mixed[]                            $includedData
     * @param null|array<string, mixed>               $meta
     * @param null|array<string, LinkMemberInterface> $links
     *
     * @throws Exception
     */
    public function resourceCollection(
        array $primaryData,
        array $includedData = null,
        array $meta = null,
        array $links = null
    ): ResourceCollectionResponse {
        $primaryResources = $this->encoder->encodeDataset($primaryData);

        return new ResourceCollectionResponse(
            new ResourceCollection($primaryResources),
            null === $includedData ? null : $this->buildIncluded($includedData),
            null === $meta ? null : new Meta($meta),
            null === $links ? null : $this->buildLinks($links)
        );
    }

    /**
     * @param null|mixed[]                            $includedData
     * @param null|array<string, mixed>               $meta
     * @param null|array<string, LinkMemberInterface> $links
     * @param mixed                                   $primaryData
     *
     * @throws Exception
     */
    public function resource(
        $primaryData,
        array $includedData = null,
        array $meta = null,
        array $links = null
    ): ResourceResponse {
        $resource = $this->encoder->encodeData($primaryData);

        return new ResourceResponse(
            $resource,
            null === $includedData ? null : $this->buildIncluded($includedData),
            null === $meta ? null : new Meta($meta),
            null === $links ? null : $this->buildLinks($links)
        );
    }

    /**
     * @param null|mixed[]                            $includedData
     * @param null|array<string, mixed>               $meta
     * @param null|array<string, LinkMemberInterface> $links
     * @param mixed                                   $primaryData
     *
     * @throws Exception
     */
    public function resourceCreated(
        $primaryData,
        array $includedData = null,
        array $meta = null,
        array $links = null
    ): ResourceCreatedResponse {
        $resource = $this->encoder->encodeData($primaryData);

        return new ResourceCreatedResponse(
            $resource,
            null === $includedData ? null : $this->buildIncluded($includedData),
            null === $meta ? null : new Meta($meta),
            null === $links ? null : $this->buildLinks($links)
        );
    }

    /**
     * @param null|mixed[]                            $includedData
     * @param null|array<string, mixed>               $meta
     * @param null|array<string, LinkMemberInterface> $links
     * @param mixed                                   $primaryData
     *
     * @throws Exception
     */
    public function resourceUpdated(
        $primaryData,
        array $includedData = null,
        array $meta = null,
        array $links = null
    ): ResourceUpdatedResponse {
        $resource = $this->encoder->encodeData($primaryData);

        return new ResourceUpdatedResponse(
            $resource,
            null === $includedData ? null : $this->buildIncluded($includedData),
            null === $meta ? null : new Meta($meta),
            null === $links ? null : $this->buildLinks($links)
        );
    }
}
