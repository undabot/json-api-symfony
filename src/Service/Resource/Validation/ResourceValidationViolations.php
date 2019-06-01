<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Service\Resource\Validation;

use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ResourceValidationViolations extends ConstraintViolationList
{
    public function __construct(
        ConstraintViolationListInterface $resourceValidationViolations,
        ConstraintViolationListInterface $attributesValidationViolations,
        ConstraintViolationListInterface $relationshipsValidationViolations
    ) {
        parent::__construct();
        $this->addAll($resourceValidationViolations);
        $this->addAll($relationshipsValidationViolations);
        $this->addAll($attributesValidationViolations);
    }
}
