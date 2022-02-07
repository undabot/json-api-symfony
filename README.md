# json-api-symfony

This library was created in order to return JSON:API compliant response using Symfony as application infrastructure provider. It will allow the user to have objects inside the application and "attach" it to response class in order to return that response class with JSON:API data structure. Also, this library gives the user possibility to handle request query parameters and parse JSON:API compliant request body into separate PHP class.

This library is wrapper around Symfony framework and it use [json-api-core](https://github.com/undabot/json-api-core) library to do heavy lifting when creating JSON:API compliant requests and responses.

# Usage

## Returning the response

### Read side

Create read model which is class with annotations which you want to return to the client. For example if you want to return this JSON:API response:

```json
{
	"links": {
		"self": "http://example.com/articles",
		"next": "http://example.com/articles?page[offset]=2",
		"last": "http://example.com/articles?page[offset]=10"
	},
	"data": [{
		"type": "articles",
		"id": "01FTZBPQ1EY5P5N3QW4ZHHFCM3",
		"attributes": {
			"title": "JSON:API Symfony rocks!"
		},
		"relationships": {
			"author": {
				"links": {
					"self": "http://example.com/articles/01FTZBPQ1EY5P5N3QW4ZHHFCM3/relationships/author",
					"related": "http://example.com/articles/01FTZBPQ1EY5P5N3QW4ZHHFCM3/author"
				},
				"data": {
					"type": "authors",
					"id": "01FTZBN5HZ590S48WM0VEYFMBY"
				}
			},
			"comments": {
				"links": {
					"self": "http://example.com/articles/01FTZBPQ1EY5P5N3QW4ZHHFCM3/relationships/comments",
					"related": "http://example.com/articles/01FTZBPQ1EY5P5N3QW4ZHHFCM3/comments"
				},
				"data": [{
						"type": "comments",
						"id": "01FTZBQRGVWX1AZG19NN0FMQZR"
					},
					{
						"type": "comments",
						"id": "01FTZBNTAB25AM278R6G3YRCKT"
					}
				]
			}
		},
		"links": {
			"self": "http://example.com/articles/01FTZBPQ1EY5P5N3QW4ZHHFCM3"
		}
	}]
}
```

You would create this model:

```php
<?php

declare(strict_types=1);

namespace App;

use App\Article;
use App\Comment;
use Undabot\SymfonyJsonApi\Model\ApiModel;
use Undabot\SymfonyJsonApi\Model\Resource\Annotation\Attribute;
use Undabot\SymfonyJsonApi\Model\Resource\Annotation\ToMany;
use Undabot\SymfonyJsonApi\Model\Resource\Annotation\ToOne;
use Undabot\SymfonyJsonApi\Service\Resource\Validation\Constraint\ResourceType;

/** @ResourceType(type="articles") */
final class ArticleReadModel implements ApiModel
{
    public function __construct(
        public readonly string $id,
        /** @Attribute */
        public readonly string $title,
        /** @ToOne(name="author", type="authors") */
        public readonly string $authorId,
        /**
         * @var array<int,string>
         * @ToMany(name="comments", type="comments")
         */
        public readonly array $commentIds,
    ) {
    }

    public static function fromSomething(Article $article): self
    {
        return new self(
            (string) $article->id(),
            (string) $article->title(),
            (string) $article->author()->id(),
            $article->comments()->map(static function (Comment $comment): string {
                return (string) $comment->id();
            })->toArray(),
        );
    }
}
```

So we have read model which have properties that reflect wanted response. Each property has annotation that states is it relation or attribute. Relations also have name and type value inside ToOne and ToMany relationship. Name value is one that we want to show in response (in our example `"relationships": {"author": {"links":` author is name we want to show as primary key for this to one relation). Type value is resource type of relation which we're referring to. Also, there is a nullable property which can be added if relation is nullable (which is bool value inside relation annotation e.g. `nullable=true` which will be like this in our example `@ToOne(name="author", type="authors", nullable=true)`). Don't forget to null safe typehint your property if you add nullable as true (e.g. `public readonly ?string $authorId`).

Other part of this class is static method `fromSomething` which will be used when creating this read model. We often use `fromEntity`, `fromValueObject` or `fromAggregate`. We fetch data from database, get entity and work with that entity through our application until last part when we need to return it. Then we pass it to this lib in order to create proper read model. In this example we assume that we have `title` method on article entity which have `__toString` method, same as id. Also, we assume that this is Doctrine entity which have `author` method which is `Author` entity and `comments` method which is `Doctrine Collection` of `Comment` objects. But this can be really any object from which you can construct given read model. 

Notes: 
 * namespaces for Article and Comment are just for demo purpose. You can place it wherever you want
 * we use readonly for properties because we don't want that value assigned during creation of the object is changed, but you don't need to do this
 * if using version of PHP prior 8.1 you can add `@psalm-immutable` annotation on the class in order to have readonly behaviour for properties
 * final class is used on the class because we don't want to extend read models, but if you need to extend it remove final declaration (although we recommend to have one read model per resource which only implements ApiModel and doesn't extend any other model).

### Responder

Responder is a class that extends `\Undabot\SymfonyJsonApi\Http\Service\Responder\AbstractResponder\AbstractResponder` and will provide array of classes mapped to callable that will accept the class of an item you send to the Responder as a key, and a callable (annon. function, factory method reference, ...) that should convert that particular data object to an API model. So create a Responder class that extends `AbstractResponder` and implement `getMap()` method which will return array as described.

```php
<?php

declare(strict_types=1);

namespace App;

use Undabot\SymfonyJsonApi\Http\Service\Responder\AbstractResponder;

final class Responder extends AbstractResponder
{
    /** {@inheritdoc} */
    public function getMap(): array
    {
        return [
            SomeClass::class => [SomeReadModel::class, 'fromSomething'],
        ];
    }
}

```

Once Responder has been created, it can be used this way

```php
<?php

declare(strict_types=1);

namespace App;

class Controller
{
    public function get(
        Responder $responder,
    ): ResourceCollectionResponse {
        ... # fetch array of entities

        return $responder->resourceCollection($entities);
    }
```
Each method accepts a data object (or collection/array of data objects) along with some other optional arguments and constructs a DTO representing the JSON:API compliant response:

* [\Undabot\SymfonyJsonApi\Http\Model\Response\ResourceCollectionResponse](src/Http/Service/Responder/AbstractResponder.php#L47)
* [\Undabot\SymfonyJsonApi\Http\Model\Response\ResourceCreatedResponse](src/Http/Service/Responder/AbstractResponder.php#L95)
* [\Undabot\SymfonyJsonApi\Http\Model\Response\ResourceUpdatedResponse](src/Http/Service/Responder/AbstractResponder.php#L119)
* [\Undabot\SymfonyJsonApi\Http\Model\Response\ResourceResponse](src/Http/Service/Responder/AbstractResponder.php#L71)
* [\Undabot\SymfonyJsonApi\Http\Model\Response\ResourceDeletedResponse](src/Http/Service/Responder/AbstractResponder.php#L136) (doesn't accept anything)

That response will then be encoded to the JSON:API compliant JSON Response by the `\Undabot\SymfonyJsonApi\Http\EventSubscriber\ViewResponseSubscriber` and will add correct HTTP status code.

### resourceCollection(...) method

```php
public function resourceCollection(
  array $primaryData, 
  array $includedData = null, 
  array $meta = null, 
  array $links = null
): ResourceCollectionResponse()
```
Accepts an array of data objects (that you have defined an encoding map entry for) and converts them to a ResourceCollectionResponse.

### resourceObjectCollection(...) method

```php
public function resourceObjectCollection(
  ObjectCollection $primaryModels,
  array $included = null,
  array $meta = null,
  array $links = null
): ResourceCollectionResponse
```
Accepts an array of objects (that you have defined an encoding map entry for) and converts them to a ResourceCollectionResponse.

### resource(...) method

```php
public function resource(
  $primaryData,
  array $includedData = null,
  array $meta = null,
  array $links = null
): ResourceResponse {
```
Accepts data (single object for example) that will be converted to a ResourceResponse. This can also be null if no data is present (e.g. somone requests `/user/1/car` and car is to one relation which is NOT present on the user because user doesn't own the car, but user with id 1 is found).

### resourceCreated(...) method

```php
public function resourceCreated(
  $primaryData,
  array $includedData = null,
  array $meta = null,
  array $links = null
): ResourceCreatedResponse
```
Accepts data (single object for example) that will be converted to a ResourceCreatedResponse.

### resourceUpdated(...) method

```php
public function resourceUpdated(
  $primaryData,
  array $includedData = null,
  array $meta = null,
  array $links = null
): ResourceUpdatedResponse
```
Accepts data (single object for example) that will be converted to a ResourceUpdatedResponse.

### resourceDeleted(...) method

```php
public function resourceDeleted(): ResourceDeletedResponse
```
ResourceDeletedResponse response will be returned which is basically [204 HTTP status](https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/204) code with no content. 

#### Includes

We often need to return objects related to given resource. In our example maybe we want to include all comments and author allowing client to read their data, not just see id and type which is returned inside relationship key (pointer to resource). So we would like to return response like this:

```json
{
	"links": {
		"self": "http://example.com/articles",
		"next": "http://example.com/articles?page[offset]=2",
		"last": "http://example.com/articles?page[offset]=10"
	},
	"data": [{
		"type": "articles",
		"id": "01FTZBPQ1EY5P5N3QW4ZHHFCM3",
		"attributes": {
			"title": "JSON:API Symfony rocks!"
		},
		"relationships": {
			"author": {
				"links": {
					"self": "http://example.com/articles/01FTZBPQ1EY5P5N3QW4ZHHFCM3/relationships/author",
					"related": "http://example.com/articles/01FTZBPQ1EY5P5N3QW4ZHHFCM3/author"
				},
				"data": {
					"type": "authors",
					"id": "01FTZBN5HZ590S48WM0VEYFMBY"
				}
			},
			"comments": {
				"links": {
					"self": "http://example.com/articles/01FTZBPQ1EY5P5N3QW4ZHHFCM3/relationships/comments",
					"related": "http://example.com/articles/01FTZBPQ1EY5P5N3QW4ZHHFCM3/comments"
				},
				"data": [{
						"type": "comments",
						"id": "01FTZBQRGVWX1AZG19NN0FMQZR"
					},
					{
						"type": "comments",
						"id": "01FTZBNTAB25AM278R6G3YRCKT"
					}
				]
			}
		},
		"links": {
			"self": "http://example.com/articles/01FTZBPQ1EY5P5N3QW4ZHHFCM3"
		}
	}],
	"included": [{
		"type": "authors",
		"id": "01FTZBN5HZ590S48WM0VEYFMBY",
		"attributes": {
			"firstName": "John",
			"lastName": "Doe",
			"twitter": "jhde"
		},
		"links": {
			"self": "http://example.com/authors/01FTZBN5HZ590S48WM0VEYFMBY"
		}
	}, {
		"type": "comments",
		"id": "01FTZBQRGVWX1AZG19NN0FMQZR",
		"attributes": {
			"body": "First!"
		},
		"relationships": {
			"author": {
				"data": {
					"type": "authors",
					"id": "01FTZBT0Q2G6G8ZGBBQ0DTSW1P"
				}
			}
		},
		"links": {
			"self": "http://example.com/comments/01FTZBQRGVWX1AZG19NN0FMQZR"
		}
	}, {
		"type": "comments",
		"id": "01FTZBNTAB25AM278R6G3YRCKT",
		"attributes": {
			"body": "I like XML better"
		},
		"relationships": {
			"author": {
				"data": {
					"type": "authors",
					"id": "01FTZBN5HZ590S48WM0VEYFMBY"
				}
			}
		},
		"links": {
			"self": "http://example.com/comments/01FTZBNTAB25AM278R6G3YRCKT"
		}
	}]
}
```

To achieve this we need to pass 2nd argument to the method of the resource responder with array of given objects. For example, we would do this in our controller for given case:

```php
<?php

declare(strict_types=1);

namespace App;

class Controller
{
    public function get(
        ArticleId $id,
        Responder $responder,
    ): ResourceCollectionResponse {
        $article = # fetch article by id
        $includes = array_merge($article->comments->toArray(), $article->author()];

        return $responder->resource($article, $includes);
    }
```

JSON:API documentation allows us to include resource into response because of 2 reasons. First one is when server returns includes because server wants that and all possible includes are returned in response for given endpoint. Second one is when client requested some (or all) of allowed includes and server returns only requested ones. When returning includes, which is basically array of objects that we want to return with main resource, we need to define read model for each included type and map them inside responder.

When returning includes inside list of objects (e.g. if we're returning list of articles inside `/articles` endpoint) we want to include only 1 resource with id. So if we have list of articles and some articles have same author, we want to include that author only once. We can use [\Undabot\SymfonyJsonApi\Model\Collection\UniqueCollection](src/Model/Collection/ObjectCollection.php#L11) for that. 

Here are examples:

```php
<?php

declare(strict_types=1);

namespace App;

use Undabot\SymfonyJsonApi\Model\Collection\UniqueCollection;

class Controller
{
    public function list(
        Responder $responder,
    ): ResourceCollectionResponse {
        $articles = # fetch array of articles
        $includes = new UniqueCollection();
        foreach ($articles as $article) {
            $includes->addObject($article->author()); // only one
            $includes->addObjects($article->comments()->toArray()); // multiple objects
        }
        // if there are many articles with same author, only 1 will be added

        return $responder->resource($article, $includes);
    }
```

```php
<?php

declare(strict_types=1);

namespace App;

use Undabot\JsonApi\Definition\Model\Request\GetResourceCollectionRequestInterface;
use Undabot\SymfonyJsonApi\Model\Collection\UniqueCollection;

class Controller
{
    public function list(
        GetResourceCollectionRequestInterface $request,
        Responder $responder,
    ): ResourceCollectionResponse {
        $request->allowIncluded(['author', 'comments']);
        $articles = # fetch array of articles
        $includes = new UniqueCollection();
        foreach ($articles as $article) {
            if (true === $request->isIncluded('author')) {
                $includes->addObject($article->author()); // only one
            }
            if (true === $request->isIncluded('comments')) {
                $includes->addObjects($article->comments()->toArray()); // multiple objects
            }
        }
        // note: you can also call $request->getIncludes() to retrieve array of all includes
        
        return $responder->resource($article, $includes);
    }
```

#### Request filters

If we want to allow and read filter from request when given endpoint is resource collection one we can do it like this:

```php
<?php

declare(strict_types=1);

namespace App;

use Undabot\JsonApi\Definition\Model\Request\GetResourceCollectionRequestInterface;
use Undabot\SymfonyJsonApi\Model\Collection\UniqueCollection;

class Controller
{
    public function list(
        GetResourceCollectionRequestInterface $request,
        Responder $responder,
    ): ResourceCollectionResponse {
        $request->allowFilters(['author.id', 'comment.ids']); // we can name this whatever we want (this could be author and comments also).
        $articles = $queryBus->handleQuery(new ArticlesQuery(
            $request->getFilterSet()?->getFilterValue('author.id'),
            $request->getFilterSet()?->getFilterValue('comment.ids'),
        ));

        return $responder->resource($article, $includes);
    }
```

#### Pagination

Currently, this library can read page and offset based pagination. No matter which one client sends to the server we can read them both like this:

```php
<?php

declare(strict_types=1);

namespace App;

use Undabot\JsonApi\Definition\Model\Request\GetResourceCollectionRequestInterface;
use Undabot\SymfonyJsonApi\Model\Collection\UniqueCollection;

class Controller
{
    public function list(
        GetResourceCollectionRequestInterface $request,
        Responder $responder,
    ): ResourceCollectionResponse {
        $pagination = $request->getPagination();
        $articles = $queryBus->handleQuery(new ArticlesQuery(
            $pagination?->getOffset(),
            $pagination?->getSize(),
        ));

        return $responder->resource($article, $includes);
    }
```

#### Sorting

We can set allowed sorting and then fetch it like this:

```php
<?php

declare(strict_types=1);

namespace App;

use Undabot\JsonApi\Definition\Model\Request\GetResourceCollectionRequestInterface;
use Undabot\SymfonyJsonApi\Model\Collection\UniqueCollection;

class Controller
{
    public function list(
        GetResourceCollectionRequestInterface $request,
        Responder $responder,
    ): ResourceCollectionResponse {
        $request->allowSorting(['article.id', 'article.createdAt', 'author.name']); // this can be any string, e.g. createdAt, article-createdAt, article_createdAt, ...
        $articles = $queryBus->handleQuery(new ArticlesQuery(
            $request->getSortSet()?->getSortsArray(),
        ));

        return $responder->resource($article, $includes);
    }
```

Notes: 
 * Given examples have a lot of logic inside controller because of readability. In real application we would recommend splitting the logic and move it into separate classes.
 * Given examples have parameters from query passed into query bus which should return array of results. You can use query bus, but you can inject repository and send parameters directly to it, you can inject database connection and make raw query with given parameters or whatever approach you use when building your applications.

### Write side

Request and response lifecycle beside read side have write side, too. Our endpoints must have option to receive data from the client. Data must be sent through body of the request as JSON string which is compliant with JSON:API. This library allows us to fetch given data, validate it and convert it to PHP class. 

So if we want to allow client to create or update our article we would first create write model:

```php
<?php

declare(strict_types=1);

namespace App;

use Undabot\SymfonyJsonApi\Model\ApiModel;
use Undabot\SymfonyJsonApi\Model\Resource\Annotation\Attribute;
use Undabot\SymfonyJsonApi\Model\Resource\Annotation\ToMany;
use Undabot\SymfonyJsonApi\Model\Resource\Annotation\ToOne;
use Undabot\SymfonyJsonApi\Service\Resource\Validation\Constraint\ResourceType;

/** @ResourceType(type="articles") */
final class ArticleWriteModel implements ApiModel
{
    public function __construct(
        public readonly string $id,
        /** @Attribute */
        public readonly string $title,
        /** @ToOne(name="author", type="authors") */
        public readonly string $authorId,
        /**
         * @var array<int,string>
         * @ToMany(name="comments", type="comments")
         */
        public readonly array $commentIds,
    ) {
    }
}
```

Notice that we don't have `fromSomething` method because this class will be created from request data, not from some class. As you see, we now have basically same properties in read and write model. If you have that case you can combine them in same model and have e.g. `ArticleModel`. Also, if your update model is same as write model, you can combine them into one and have one write (for create and update) and one read model. Notice that update model needs to have `fromSomething` method because we first need to create model from existing data. So in our example above we'll need to add that static method if we're going to use it for write and update.

When request come into the controller we'll have to inject request and create write model from sent data. Here is example in which we'll use [SimpleResourceHandler](/src/Http/Service/SimpleResourceHandler.php#L13) and [CreateResourceRequestInterface](https://github.com/undabot/json-api-core/blob/master/src/Definition/Model/Request/CreateResourceRequestInterface.php) (and its concrete implementation) for help.

```php
<?php

declare(strict_types=1);

namespace App;

use Undabot\JsonApi\Definition\Model\Request\CreateResourceRequestInterface;
use Undabot\SymfonyJsonApi\Http\Service\SimpleResourceHandler;

class Controller
{
    public function create(
        CreateResourceRequestInterface $request,
        SimpleResourceHandler $resourceHandler,
        Responder $responder,
    ): ResourceCollectionResponse {
        /** @var ArticleWriteModel $articleWriteModel */
        $articleWriteModel = $resourceHandler->getModelFromRequest(
            $request,
            ArticleWriteModel::class,
        );
        // now you can use something like
        $articleWriteModel->title;
        
        return $responder->resourceCreated($article, $includes);
    }
```

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
