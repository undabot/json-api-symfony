# json-api-symfony

[![pipeline status](https://gitlab.com/undabot/json-api-symfony/badges/master/pipeline.svg)](https://gitlab.com/undabot/json-api-symfony/commits/master)
[![coverage report](https://gitlab.com/undabot/json-api-symfony/badges/master/coverage.svg)](https://gitlab.com/undabot/json-api-symfony/commits/master)


```
"repositories": [
    {
      "type": "vcs",
      "url": "git@gitlab.com:undabot/json-api-symfony.git"
    }
  ],
```

# Usage

## Responder

First create a Responder class that extends `AbstractResponder` and implement `getMap()` method. `getMap()` method should return array of classes mapped to callable

```php
<?php

declare(strict_types=1);

use Undabot\SymfonyJsonApi\Http\Service\Responder\AbstractResponder;

class CmsResponder extends AbstractResponder
{
    /**
     * @return array<string, callable>
     */
    public function getMap(): array
    {
        return [
            Entity::class => [EntityReadModel::class, 'fromEntity'],
        ];
    }
}

```

Once Responder has been created, it can be used this way

```php
<?php

class Controller
{
    public function get(
        CmsResponder $responder
    ): ResourceCollectionResponse {
        ... # fetch array of entities entities

        return $responder->resourceCollection(
            $entities
        );
    }
```

Here is the list of possible methods from AbstractResponder

* [resourceCollection](src/Http/Service/Responder/AbstractResponder.php#L47)
* [resource](https://gitlab.com/undabot/json-api-symfony/blob/feature/custom-map/src/Http/Service/Responder/AbstractResponder.php#L71)
* [resourceUpdated](https://gitlab.com/undabot/json-api-symfony/blob/feature/custom-map/src/Http/Service/Responder/AbstractResponder.php#L119)
* [resourceCreated](https://gitlab.com/undabot/json-api-symfony/blob/feature/custom-map/src/Http/Service/Responder/AbstractResponder.php#L95)

# Configuration
Exception listener has default priority of -128 but it can be configured by creating `config/packages/json_api_symfony.yaml` with following parameters

```yaml
json_api_symfony:
    exception_listener_priority: 100
``` 

# Naming

## Entity
Domain object that is modeled and used in the application. Has nothing to do with the outer world.

## (API) Model
Domain representation for specific API. Data-transfer object, POPO that contains only values of attributes and identifiers of related resources. 

## (JSON:API) Resource
Object representation of JSON:API resource defined by the JSON:API specification.

# Data conversion flow

<img src='https://g.gravizo.com/svg?
digraph G {
resource;
model;
entity;
entity -> model [label="Api model construction"];
model -> resource [label="JSON:API serialize"];
resource -> model [label="JSON:API denormalize"];
model -> entity [label="Commands"];
  }
'>
