<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Php80\ValueObject\AnnotationToAttribute;
use Rector\Php81\Rector\FuncCall\NullToStrictStringFuncCallArgRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Symfony\Set\SymfonySetList;

return RectorConfig::configure()
    ->withPaths([__DIR__ . '/src', __DIR__ . '/tests'])
    ->withPhpSets(php84: true)
    ->withSets([
        PHPUnitSetList::ANNOTATIONS_TO_ATTRIBUTES,
        PHPUnitSetList::PHPUNIT_100,
        PHPUnitSetList::PHPUNIT_110,
        PHPUnitSetList::PHPUNIT_120,
        SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES,
        DoctrineSetList::ANNOTATIONS_TO_ATTRIBUTES,
    ])
    ->withConfiguredRule(AnnotationToAttributeRector::class, [
        new AnnotationToAttribute('Undabot\SymfonyJsonApi\Model\Resource\Annotation\Attribute'),
        new AnnotationToAttribute('Undabot\SymfonyJsonApi\Model\Resource\Annotation\ToOne'),
        new AnnotationToAttribute('Undabot\SymfonyJsonApi\Model\Resource\Annotation\ToMany'),
        new AnnotationToAttribute('Undabot\SymfonyJsonApi\Service\Resource\Validation\Constraint\ResourceType'),
        new AnnotationToAttribute('Undabot\SymfonyJsonApi\Service\Resource\Validation\Constraint\ToOne'),
        new AnnotationToAttribute('Undabot\SymfonyJsonApi\Service\Resource\Validation\Constraint\ToMany'),
    ])
    ->withSkip([
        NullToStrictStringFuncCallArgRector::class,
    ]);
