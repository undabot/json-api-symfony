<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Http\Service\ModelEncoder;

/**
 * This class is implemented to allow rapid development by providing mapping definition between data
 * objects and their ApiModels.
 *
 * JSON:API Bundle will process the mappings and use it in Encoder (and therefore Responder) classes
 * to determine the ApiModel class that should be constructed for given object.
 *
 * `getMap()` method should return array of elements: `data class => callable factory function`.
 * The Factory function will be called with one argument, instance of data object.
 *
 * @see \Undabot\SymfonyJsonApi\Http\Service\ModelEncoder\MappedModelEncoder
 * @see \Undabot\SymfonyJsonApi\Http\Service\Responder\ModelResponder
 * @see \Undabot\SymfonyJsonApi\Http\Service\Responder\CollectionResponder
 */
interface ModelEncoderMapInterface
{
    public function getMap(): array;
}
