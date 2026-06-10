<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema\Resource;

class ResourceSchemaSet
{
    public function __construct(private readonly ?IdentifierSchema $identifier, private readonly ?ReadSchema $readModel, private readonly ?CreateSchema $createModel, private readonly ?UpdateSchema $updateModel) {}

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
