<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Http\Service\ModelEncoder;

use Undabot\JsonApi\Model\Resource\ResourceInterface;

interface EncoderInterface
{
    /** @param mixed $data */
    public function encodeData($data, callable $modelTransformer): ResourceInterface;
}
