<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Http\Service\Responder;

use Assert\Assertion;
use Undabot\JsonApi\Definition\Model\Link\LinkMemberInterface;
use Undabot\JsonApi\Implementation\Model\Link\Link;
use Undabot\JsonApi\Implementation\Model\Link\LinkCollection;
use Undabot\JsonApi\Implementation\Model\Meta\Meta;
use Undabot\JsonApi\Implementation\Model\Resource\ResourceCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Proxy\Proxy;
use Exception;
use RuntimeException;
use Undabot\SymfonyJsonApi\Http\Model\Response\ResourceCollectionResponse;
use Undabot\SymfonyJsonApi\Http\Model\Response\ResourceCreatedResponse;
use Undabot\SymfonyJsonApi\Http\Model\Response\ResourceResponse;
use Undabot\SymfonyJsonApi\Http\Model\Response\ResourceUpdatedResponse;
use Undabot\SymfonyJsonApi\Http\Service\ModelEncoder\DataEncoder;

abstract class AbstractResponder
{
    /** @var EntityManagerInterface */
    private $entityManager;
    /** @var DataEncoder */
    private $dataEncoder;

    public function __construct(
        EntityManagerInterface $entityManager,
        DataEncoder $modelEncoder
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
        $resource = $this->encodeData($primaryData);

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

    /**
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
        if ( ! isset($map[$dataClass])) {
            $message = sprintf(
                'Couldn\'t resolve transformer class for object of class `%s` given. Have you defined data transformer for that data class?',
                $dataClass
            );

            throw new RuntimeException($message);
        }

        return $map[$dataClass];
    }
}
