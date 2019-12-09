<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema\Resource;

use JsonApiOpenApi\Model\OpenApi\ResourceSchemaInterface;

class ResourceSchemaSet
{
    /** @var IdentifierSchema|null */
    private $identifier;

    /** @var ReadSchema|null */
    private $readModel;

    /** @var CreateSchema|null */
    private $createModel;

    /** @var UpdateSchema|null */
    private $updateModel;

    public function __construct(
        ?IdentifierSchema $identifier,
        ?ResourceSchemaInterface $readModel,
        ?ResourceSchemaInterface $createModel,
        ?ResourceSchemaInterface $updateModel
    ) {
        $this->identifier = $identifier;
        $this->readModel = $readModel;
        $this->createModel = $createModel;
        $this->updateModel = $updateModel;
    }

    public function getIdentifier(): ?IdentifierSchema
    {
        return $this->identifier;
    }

    public function getReadModel(): ?ReadSchema
    {
        return $this->readModel;
    }

    public function getCreateModel(): ?CreateSchema
    {
        return $this->createModel;
    }

    public function getUpdateModel(): ?UpdateSchema
    {
        return $this->updateModel;
    }
}
