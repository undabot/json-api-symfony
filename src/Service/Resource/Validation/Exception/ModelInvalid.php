<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Service\Resource\Validation\Exception;

use Undabot\JsonApi\Definition\Model\Resource\ResourceInterface;
use Undabot\SymfonyJsonApi\Service\Resource\Validation\ResourceValidationViolations;

class ModelInvalid extends \Exception
{
    /** @var ResourceInterface */
    private $resource;

    /** @var ResourceValidationViolations */
    private $violations;

    public function __construct(
        ResourceInterface $resource,
        ResourceValidationViolations $violations
    ) {
        parent::__construct();
        $this->resource = $resource;
        $this->violations = $violations;
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
