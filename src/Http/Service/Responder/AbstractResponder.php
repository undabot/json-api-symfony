<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Http\Service\Responder;

use Assert\Assertion;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Proxy\Proxy;
use Exception;
use RuntimeException;
use Undabot\JsonApi\Definition\Model\Link\LinkMemberInterface;
use Undabot\JsonApi\Definition\Model\Resource\ResourceInterface;
use Undabot\JsonApi\Implementation\Model\Link\Link;
use Undabot\JsonApi\Implementation\Model\Link\LinkCollection;
use Undabot\JsonApi\Implementation\Model\Meta\Meta;
use Undabot\JsonApi\Implementation\Model\Resource\ResourceCollection;
use Undabot\SymfonyJsonApi\Http\Model\Response\ResourceCollectionResponse;
use Undabot\SymfonyJsonApi\Http\Model\Response\ResourceCreatedResponse;
use Undabot\SymfonyJsonApi\Http\Model\Response\ResourceDeletedResponse;
use Undabot\SymfonyJsonApi\Http\Model\Response\ResourceResponse;
use Undabot\SymfonyJsonApi\Http\Model\Response\ResourceUpdatedResponse;
use Undabot\SymfonyJsonApi\Http\Service\ModelEncoder\EncoderInterface;
use Undabot\SymfonyJsonApi\Model\Collection\ObjectCollection;

abstract class AbstractResponder
{
    /** @var EntityManagerInterface */
    private $entityManager;
    /** @var EncoderInterface */
    private $dataEncoder;

    public function __construct(
        EntityManagerInterface $entityManager,
        EncoderInterface $modelEncoder
    ) {
        $this->entityManager = $entityManager;
        $this->dataEncoder = $modelEncoder;
    }

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
        $primaryResources = $this->encodeDataset($primaryData);

        return new ResourceCollectionResponse(
            new ResourceCollection($primaryResources),
            null === $includedData ? null : $this->buildIncluded($includedData),
            null === $meta ? null : new Meta($meta),
            null === $links ? null : $this->buildLinks($links)
        );
    }

    /**
     * Opinionated collection responder that adds meta information about total count of models, used for pagination.
     *
     * @param null|array<string, mixed>               $meta
     * @param null|array<string, LinkMemberInterface> $links
     *
     * @throws Exception
     */
    public function resourceObjectCollection(
        ObjectCollection $primaryModels,
        array $included = null,
        array $meta = null,
        array $links = null
    ): ResourceCollectionResponse {
        $primaryResources = $this->encodeDataset($primaryModels->getItems());
        $meta = $meta ?? ['total' => $primaryModels->count()];

        return new ResourceCollectionResponse(
            new ResourceCollection($primaryResources),
            null === $included ? null : $this->buildIncluded($included),
            new Meta($meta),
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
        /**
         * resource response can be single resource or null.
         *
         * @see https://jsonapi.org/format/#fetching-resources-responses-200
         */
        $resource = null === $primaryData ? null : $this->encodeData($primaryData);

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
        $resource = $this->encodeData($primaryData);

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
        $resource = $this->encodeData($primaryData);

        return new ResourceUpdatedResponse(
            $resource,
            null === $includedData ? null : $this->buildIncluded($includedData),
            null === $meta ? null : new Meta($meta),
            null === $links ? null : $this->buildLinks($links)
        );
    }

    public function resourceDeleted(): ResourceDeletedResponse
    {
        return new ResourceDeletedResponse();
    }

    /**
     * This method allows for rapid development by providing mapping definition between data
     * objects and their ApiModels.
     *
     * JSON:API Bundle will process the mappings and use it in Responder
     * to determine the ApiModel class that should be constructed for given object.
     *
     * `getMap()` method should return array of elements: `data class => callable factory function`.
     * The Factory function will be called with one argument, instance of data object.
     *
     * @return array<string, callable>
     */
    abstract protected function getMap(): array;

    /**
     * @param mixed $data
     *
     * @throws Exception
     */
    private function encodeData($data): ResourceInterface
    {
        $dataTransformer = $this->getDataTransformer($data);

        return $this->dataEncoder->encodeData($data, $dataTransformer);
    }

    /**
     * @param mixed[] $data
     *
     * @return ResourceInterface[]
     */
    private function encodeDataset(array $data): array
    {
        return array_map([$this, 'encodeData'], $data);
    }

    /**
     * @param array<string, LinkMemberInterface> $links
     */
    private function buildLinks(array $links): LinkCollection
    {
        Assertion::allIsInstanceOf($links, LinkMemberInterface::class);
        $constructedLinks = [];
        foreach ($links as $linkName => $linkValue) {
            $constructedLinks[] = new Link($linkName, $linkValue);
        }

        return new LinkCollection($constructedLinks);
    }

    /**
     * @param mixed[] $dataSet
     *
     * @throws \Exception
     */
    private function buildIncluded(array $dataSet): ResourceCollection
    {
        $includedResources = $this->encodeDataset($dataSet);

        return new ResourceCollection($includedResources);
    }

    /**
     * Returns factory callable that will transform given $data object to ApiModel by following defined
     * encoding rules (encoding map).
     *
     * Supports resolving Doctrine proxy classes to actuall entity class names.
     *
     * @param object $data
     */
    private function getDataTransformer($data): callable
    {
        $dataClass = \get_class($data);

        // Support Doctrine Entities that are usually represented as Proxy classes.
        // Resolve exact class name before looking up in the encoders map.
        if ($data instanceof Proxy) {
            $dataClass = $this->entityManager->getClassMetadata($dataClass)->name;
        }

        $map = $this->getMap();
        if (!isset($map[$dataClass])) {
            $message = sprintf(
                'Couldn\'t resolve transformer class for object of class `%s` given. Have you defined data transformer for that data class?',
                $dataClass
            );

            throw new RuntimeException($message);
        }

        return $map[$dataClass];
    }
}
