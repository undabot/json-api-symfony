<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Resource\Factory;

use Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EntityResourceFactoryResolver
{
    /** @var array */
    private $mappings = [];

    /** @var ContainerInterface */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function addMapping(string $entityClass, string $factoryClass)
    {
        if (false === $this->container->has($factoryClass)) {
            $message = sprintf('Entity Resource Factory `%s` is not defined as a service', $factoryClass);
            throw new Exception($message);
        }

        $this->mappings[$entityClass] = $factoryClass;
    }

    public function resolveObject($object): EntityToResourceFactoryInterface
    {
        return $this->resolve(get_class($object));
    }

    public function resolve(string $class): EntityToResourceFactoryInterface
    {
        if (false === array_key_exists($class, $this->mappings)) {
            $message = sprintf('Entity Resource Factory for class `%s` is not defined', $class);
            throw new Exception($message);
        }

        /** @var EntityToResourceFactoryInterface $factory */
        $factory = $this->container->get($this->mappings[$class]);

        return $factory;
    }
}
