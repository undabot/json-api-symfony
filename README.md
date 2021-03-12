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

First create a Responder class that extends `AbstractResponder` and implement `getMap()` method. `getMap()` method should return array of classes mapped to callable that will accept the class of an item you send to the Responder as a key, and a callable (annon. function, factory method reference, ...) that should convert that particular data object to an API model (i.e. the DTO).

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
        ... # fetch array of entities

        return $responder->resourceCollection(
            $entities
        );
    }
```
Each method accepts a data object (or collection/array of data objects) along with some other optional arguments and constructs a DTO representing the JSON:API compliant response:

* [\Undabot\SymfonyJsonApi\Http\Model\Response\ResourceCollectionResponse](src/Http/Service/Responder/AbstractResponder.php#L47)
* [\Undabot\SymfonyJsonApi\Http\Model\Response\ResourceCreatedResponse](src/Http/Service/Responder/AbstractResponder.php#L95)
* [\Undabot\SymfonyJsonApi\Http\Model\Response\ResourceUpdatedResponse](src/Http/Service/Responder/AbstractResponder.php#L119)
* [\Undabot\SymfonyJsonApi\Http\Model\Response\ResourceResponse](src/Http/Service/Responder/AbstractResponder.php#L71)
* [\Undabot\SymfonyJsonApi\Http\Model\Response\ResourceDeletedResponse](src/Http/Service/Responder/AbstractResponder.php#L136) (doesn't accept anything)

That response will then be encoded to the JSON:API compliant JSON Response by the `\Undabot\SymfonyJsonApi\Http\EventSubscriber\ViewResponseSubscriber`


### resourceCollection(...) method

```php
public function resourceCollection(
  array $primaryData, 
  array $includedData = null, 
  array $meta = null, 
  array $links = null
): ResourceCollectionResponse()
```
Accepts an array of entities (or any other data objects you have defined a encoding map entry for) and converts them to a ResourceCollectionResponse.

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

# Development

There is a custom docker image that can be used for development. 
This docker container should be used to run tests and check for any compatibility issues.

This repo is mounted inside of the container and any changes made to the files are automatically propagated into the container.
There isnt any syncing, the filesystem is pointed to the 2 locations at the same time.

A script called dev.sh can be used to manage the image. Here are the avaliable commands:

- ./dev.sh build

      used to build base dev docker image, and to install composer and dependencies at first run
- ./dev.sh run

      starts the dev container
- ./dev.sh stop

      stops the dev container
- ./dev.sh ssh

      attaches the container shell to the terminal so that you can execute commands inside of the container
- ./dev.sh test
      
      run php unit tests inside of the running container
- ./dev.sh qc

      executes qc tests

- ./dev.sh install
      executes composer install --optimize-autoloader
