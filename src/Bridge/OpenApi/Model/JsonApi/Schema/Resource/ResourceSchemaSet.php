<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema\Resource;

class ResourceSchemaSet
{
    /** @var null|IdentifierSchema */
    private $identifier;

    /** @var null|ReadSchema */
    private $readModel;

    /** @var null|CreateSchema */
    private $createModel;

    /** @var null|UpdateSchema */
    private $updateModel;

    public function __construct(
        ?IdentifierSchema $identifier,
        ?ReadSchema $readModel,
        ?CreateSchema $createModel,
        ?UpdateSchema $updateModel
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
