<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema\Resource;

class ResourceSchemaSet
{
    public function __construct(private ?IdentifierSchema $identifier, private ?ReadSchema $readModel, private ?CreateSchema $createModel, private ?UpdateSchema $updateModel) {}

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
