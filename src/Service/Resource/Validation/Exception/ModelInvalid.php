<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Service\Resource\Validation\Exception;

use Undabot\JsonApi\Definition\Model\Resource\ResourceInterface;
use Undabot\SymfonyJsonApi\Service\Resource\Validation\ResourceValidationViolations;

class ModelInvalid extends \Exception
{
    public function __construct(
        private ResourceInterface $resource,
        private ResourceValidationViolations $violations
    ) {
        parent::__construct();
    }

    public function getResource(): ResourceInterface
    {
        return $this->resource;
    }

    public function getViolations(): ResourceValidationViolations
    {
        return $this->violations;
    }
}
