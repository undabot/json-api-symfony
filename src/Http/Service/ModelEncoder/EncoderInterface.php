<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Http\Service\ModelEncoder;

use Undabot\JsonApi\Definition\Model\Resource\ResourceInterface;

interface EncoderInterface
{
    public function encodeData(mixed $data, callable $modelTransformer): ResourceInterface;
}
