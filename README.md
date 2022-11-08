# json-api-symfony

This library was created with the idea of returning JSON:API compliant responses using Symfony as an application infrastructure provider. Library provides developers with support for having objects inside the application and "attaching" them to the response class. The end result is a response class with JSON:API data structure. The library also provides support for handling request query parameters and parsing JSON:API compliant request body into separate PHP classes.

The library itself is a wrapper around the Symfony framework, and it uses [json-api-core](https://github.com/undabot/json-api-core) library to do the heavy lifting when creating JSON:API compliant requests and responses.

This document covers following sections:
- [Usage](#usage)
  - [How to return the JSON:API compliant response?](#return-response)
  - [Write side](#write-side)
  - [Read side](#read-side)
  - [Responder](#responder)
- [Configuration](#configuration)
- [Development](#development)
- [Glossary](#glossary)

## <a name='usage'></a>Usage

This section covers usage examples and a detailed explanation of library elements. After going through it, you will have more than sufficient knowledge of using the library, without the need for further help.
- - -
_Before we proceed, here are a few notes:_
* _Namespaces for Article and Comment are just for demo purposes. You can place it wherever you want._
* _We use `readonly` for properties because we don't want that value assigned during the creation of the object is changed, but you don't need to do this. If using a version of PHP prior 8.1 you can add `@psalm-immutable` (read more about Psalm [here](https://github.com/vimeo/psalm)) annotation on the class to have readonly behaviour for properties._
* _Final class is used on the class because we don't want to extend read models, but if you need to extend it remove final declaration (although we recommend to have one read model per resource which only implements ApiModel and doesn't extend any other model)._
* _Given examples have a lot of logic inside the controller because of readability. In a real application, we would recommend splitting the logic and move it into separate classes._
* _Given examples have parameters from query passed into query bus, which should return an array of results. Instead of using the query bus, you can inject the repository and send parameters directly to it or even inject a database connection and make a raw query with the given parameters. Use whatever approach you use when building your applications._
- - -

### <a name='return-response'></a>How to return the JSON:API compliant response?

To return JSON:API compliant response, you have to go through a couple of steps - what you need is read or write side and responder. Both read and write sides logically consist of several classes: 
* the controller, an entry point of the request 
* the model used to create the entity or return requested information
* the entity that stores the data 

Responder serves as a glue, mapping the entities to models.

Before going deeper into the read and write models, responders and controllers, it's a good idea to describe how we distinguish attributes from relations in our models. To recognise which property is attribute and which one is relation we use annotations. Each model should have one "main" annotation that determines its type, a top-level member for any resource object. This annotation is placed just above the class declaration, and it looks like this:

```php
/** @ResourceType(type="articles") */
final class ArticleWriteModel implements ApiModel
```

Apart from `@ResourceType` annotation, there are three more - `@Attribute`, `@ToOne` and `@ToMany`.

`@Attribute` annotation says that the property is considered an attribute.

`@ToOne` and `@ToMany` annotations say that the property is a relationship. Relations must consist of name and type value inside ToOne and ToMany annotations, e.g.

```php
/**
  * @var array<int,string>
  * @ToMany(name="article_comments", type="comments")
 */
public readonly array $commentIds,
```

**Name** value is what we want to show in response. For this example, `article_comments` is the name for this relationship that will be returned in the response. If no name is defined in the annotation the relationship will inherit the property name.\
**Type** value is the resource type of relation to which we're referring. Here, we're referring to comments, meaning that a model of type comments related to this model is part of the codebase. Keep in mind that the library links only types of the exact name, so if your model is of type `comment`, and you make a mistake and write plural library will throw an error.

Relationships can be nullable, and to add a nullable relationship to the model, you just need to assign a bool value to `nullable` property inside the annotation, like in the following example. Don't forget to null safe type-hint your property in that case, and remember - relationships are not nullable by default.

```php
/**
  * @var array<int,string>
  * @ToOne(name="article_author", type="authors", nullable=true)
 */
public readonly ?string $authorId,
```

With this knowledge, lets dive into the more concrete examples.


### <a name='write-side'></a>Write side

The request and response lifecycle consists of receiving the data from the client and returning the data to the client - write and read side. When receiving data, it must be sent through the request's body as a JSON string compliant with JSON:API. This library allows us to fetch given data, validate it and convert it to PHP class.

#### Create

The write model consists of the annotated properties that build the resource we are about to create. So if we're about to create article with id, title and some related comments this is how the create (write) model would look like. 

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

This class is created entirely from request data, not from another class, so it has only constructor. Each property has an annotation stating whether it is a relation or an attribute. It is important that each relation has its name and type value inside `ToOne` and `ToMany` annotation.\
As you will see later in the read model, we will usually have the same properties in the read and write model. So if you have that case, you can combine them in the same model and have, e.g. `ArticleModel`. Also, if your update model is the same as the write model, you can combine them into one and have one write model (for create and update), and one read model.

Now when you know how to create a model for your write side, let's see what else we need. Suppose you already have an article entity what we're missing here is a controller.\
To extract the data from the request into the model, we'll have to inject the entire request. Below is an example in which we'll use [SimpleResourceHandler](/src/Http/Service/SimpleResourceHandler.php#L13) and [CreateResourceRequestInterface](https://github.com/undabot/json-api-core/blob/master/src/Definition/Model/Request/CreateResourceRequestInterface.php) (and its concrete implementation) for help.

```php
<?php

declare(strict_types=1);

namespace App;

use Undabot\JsonApi\Definition\Model\Request\CreateResourceRequestInterface;
use Undabot\SymfonyJsonApi\Http\Model\Response\ResourceCreatedResponse;
use Undabot\SymfonyJsonApi\Http\Service\SimpleResourceHandler;

class Controller
{
    public function create(
        CreateResourceRequestInterface $request,
        SimpleResourceHandler $resourceHandler,
        Responder $responder,
    ): ResourceCreatedResponse {
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

#### Update

When updating a resource, the client may send you some of the fields that are part of the read model, not an entire model. E.g. if we're updating Article with content, there is no need to send the title or some other property. However, for the mentioned case, we need some model and need to have the option to create it from the current state. So we can use the write model from the above example, but we'll have to add `fromSomething` method and then use it inside the controller like in the example below.

```php
<?php

declare(strict_types=1);

namespace App;

use Undabot\JsonApi\Definition\Model\Request\UpdateResourceRequestInterface;
use Undabot\SymfonyJsonApi\Http\Model\Response\ResourceUpdatedResponse;
use Undabot\SymfonyJsonApi\Http\Service\SimpleResourceHandler;
use Undabot\SymfonyJsonApi\Service\Resource\Factory\ResourceFactory;

class Controller
{
    public function update(
        ArticleId $id,
        UpdateResourceRequestInterface $request,
        SimpleResourceHandler $resourceHandler,
        Responder $responder,
        ResourceFactory $resourceFactory,
    ): ResourceUpdatedResponse {
        $article = // fetch article by id
        $baseModel = ArticleWriteModel::fromEntity($article);
        $baseResource = $resourceFactory->make($baseModel);
        $updateResource = new CombinedResource($baseResource, $request->getResource());

        /** @var ArticleWriteModel $articleUpdateModel */
        $articleUpdateModel = $resourceHandler->getModelFromResource(
            $updateResource,
            ArticleWriteModel::class,
        );
        // now you can use something like
        $articleUpdateModel->title;
        
        return $responder->resourceUpdated($article, $includes);
    }
```

If you are to use the same model to create and update resource, this model needs to have `fromSomething` method. As mentioned above, you can call it `ArticleModel`, and it would look like this:

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
final class ArticleModel implements ApiModel
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

In practice, `fromSomething` method is called `fromEntity` since we mostly create update/read models from entities. If you, for example, use view models then you can write it like this:

```php
public static function fromEntity(Article $article): self
    {
        $viewModel = $article->viewModel();

        return new self(
            $viewModel->id,
            $viewModel->title,
            $viewModel->authorId,
            $viewModel->commentIds,
        );
    }
```

### <a name='read-side'></a>Read side

Like the write model, the read model is a class with annotated properties that you want to return to the client. E.g. if you need to return this JSON:API response:

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

Same as in create and/or update model, read model consists of properties with annotations. Each property has annotation that states whether is it a relation or attribute. 

Another part of this class is the static method `fromSomething` used when creating the read model. As previously mentioned, we often use `fromEntity` naming. In addition, we often use other namings for this method, such as `fromValueObject` or `fromAggregate`.
The data flow that we usually use is similar to this:
1. We fetch data from the database as an entity and work with that entity through our application.
2. We use the entity until the moment in which we need to return the response.
3. At that moment, we pass the entity to this library to create a proper read model.

With everything previously written we have covered the basic model. What if the model is not so basic, and it requires includes, sorting, filtering, etc? 

#### Includes

We often need to return objects related to the given resource. Maybe our client needs more than id and type of the relation, and we need to include the details of all comments and author in our example. E.g. our client needs a response similar to the one below.

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

To return more than just a resource pointer to the client, we need to pass an array of objects that we need to return as the second argument of the called method. For example, if we need to include details of all comments related to the article, we would pass an array of comment entities as a second argument of the method called from the Responder. Like this:

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
        // use toArray() method since comments are a collection
        return $responder->resource($article, $article->comments->toArray());
    }
```

Whether the client requested explicitly requested includes, or the response needs to have all the possible includes, Responder needs to have the mapping for the read model of each object that is returned in the response.

When returning includes inside a list of objects (e.g. returning a list of articles from the `/articles` endpoint) there is a chance that different resources will have same relations. In that case we want to include the relation only once. E.g., if we have a list of articles and some articles have the same author, we want to include that author only once. We can use [\Undabot\SymfonyJsonApi\Model\Collection\UniqueCollection](src/Model/Collection/ObjectCollection.php#L11) for that.

Here are some examples:

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

If the endpoint requires filters, this is how to add them. First, allow the filters by calling the `allowFilters` method. This method receives an array of strings, which are actually filter names. After allowing the filters you can read their values by calling `$request->getFilterSet()?->getFilterValue('filter_name')`. Keep in mind that filtering is supported on endpoints that deal with collections.

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

The library can currently read page and offset based pagination. Page based pagination is automatically converted to an offset based pagination. So no matter which one the client sends to the server, we can read them both like in the example below.

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

Similar to filtering, sorting needs to be allowed first. The example below shows how to enable and read sorting values.

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

#### Fields

We can allow client calling only some fields:

```php
<?php

declare(strict_types=1);

namespace App;

use Undabot\JsonApi\Definition\Model\Request\GetResourceRequestInterface;

class Controller
{
    public function get(
        ArticleId $id,
        Responder $responder,
        GetResourceRequestInterface $request,
    ): ResourceCollectionResponse {
        $request->allowFields(['title']);
        // now it's up to you will you fetch resource with only given fields
        // or you'll fetch entire resource and strip fields in read
        // model (make separate read model for all fields combination)
        $article = # fetch article by id

        return $responder->resource($article);
    }
```

### <a name='responder'></a>Responder

Responder is a glue that we need to link the entities with their models. It holds the array of (entity) classes mapped to a callable inside a model.

Every Responder you create, and you can have more of them in your project, should extend `\Undabot\SymfonyJsonApi\Http\Service\Responder\AbstractResponder\AbstractResponder`class and implement `getMap()` method in the following way.

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
This is how the Responder is used in a controller to return the single resource (`getById` endpoint). 
```php
<?php

declare(strict_types=1);

namespace App;

class Controller
{
    public function get(
        Responder $responder,
    ): ResourceResponse {
        ... # fetch single entity

        return $responder->resource($singleEntity);
    }
```
This is how the Responder is used in a controller to return the collection of resources (`list` endpoint). Responder will convert each entity from the array of entites to read model.
```php
<?php

declare(strict_types=1);

namespace App;

class Controller
{
    public function list(
        Responder $responder,
    ): ResourceCollectionResponse {
        ... # fetch array of entities

        return $responder->resourceCollection($entities);
    }
```
As you can see, Responder supports several different methods, which you can use depending on the response you need to return to the client. Each method accepts a data object (or collection/array of data objects) along with some other optional arguments and constructs a DTO representing the JSON:API compliant response. This is the list of the supported methods:

* [\Undabot\SymfonyJsonApi\Http\Model\Response\ResourceCollectionResponse](src/Http/Service/Responder/AbstractResponder.php#L47)
* [\Undabot\SymfonyJsonApi\Http\Model\Response\ResourceCreatedResponse](src/Http/Service/Responder/AbstractResponder.php#L95)
* [\Undabot\SymfonyJsonApi\Http\Model\Response\ResourceUpdatedResponse](src/Http/Service/Responder/AbstractResponder.php#L119)
* [\Undabot\SymfonyJsonApi\Http\Model\Response\ResourceResponse](src/Http/Service/Responder/AbstractResponder.php#L71)
* [\Undabot\SymfonyJsonApi\Http\Model\Response\ResourceDeletedResponse](src/Http/Service/Responder/AbstractResponder.php#L136) (doesn't accept anything)

ViewResponseSubscriber (`\Undabot\SymfonyJsonApi\Http\EventSubscriber\ViewResponseSubscriber`) will then encode the response generated by the Responder to the JSON:API compliant JSON response. Furthermore, it will add the correct HTTP status code to the response, e.g. `201` if the `ResourceCreatedMethod` has been called from the Responder, or `204` if you have called `ResourceDeletedResponse`.

### resourceCollection(...) method

Accepts an array of data objects you have defined an encoding map entry for in the Responder and converts them to a ResourceCollectionResponse.
```php
public function resourceCollection(
  array $primaryData, 
  array $includedData = null, 
  array $meta = null, 
  array $links = null
): ResourceCollectionResponse()
```

### resourceObjectCollection(...) method

Accepts an array of objects you have defined an encoding map entry for in the Responder and converts them to a ResourceCollectionResponse.
```php
public function resourceObjectCollection(
  ObjectCollection $primaryModels,
  array $included = null,
  array $meta = null,
  array $links = null
): ResourceCollectionResponse
```

### resource(...) method

Accepts data, e.g. single object that will be converted to a ResourceResponse. Data can also be null if no data is present. For example, if someone requests `/user/1/car` and car is to one relation which is NOT present on the user because the user doesn't own the car. In this example, the user with id 1 exists in the database.

```php
public function resource(
  $primaryData,
  array $includedData = null,
  array $meta = null,
  array $links = null
): ResourceResponse {
```

### resourceCreated(...) method

Accepts data, e.g. single object that will be converted to a ResourceCreatedResponse.

```php
public function resourceCreated(
  $primaryData,
  array $includedData = null,
  array $meta = null,
  array $links = null
): ResourceCreatedResponse
```

### resourceUpdated(...) method

Accepts data, e.g. single object that will be converted to a ResourceUpdatedResponse.

```php
public function resourceUpdated(
  $primaryData,
  array $includedData = null,
  array $meta = null,
  array $links = null
): ResourceUpdatedResponse
```

### resourceDeleted(...) method

ResourceDeletedResponse response is basically [204 HTTP status](https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/204) code with no content.

```php
public function resourceDeleted(): ResourceDeletedResponse
```

## <a name="configuration"></a>Configuration
Exception listener has default priority of -128 but it can be configured by creating `config/packages/json_api_symfony.yaml` with following parameters

```yaml
json_api_symfony:
    exception_listener_priority: 100
```

## <a name="development"></a>Development

There is a custom docker image that you can use for development.
This repository is mounted inside the container, and any changes made to the files are automatically propagated into the container. You can use the container to run tests and check for compatibility issues.
There isn't any syncing since the filesystem points to the 2 locations simultaneously.

Use the script called `dev.sh` to manage the image. Here are the available commands:
- **Build** base dev docker image, and to install composer and dependencies at first run

      ./dev.sh build

- **Start** the dev container

      ./dev.sh run

- **Stop** the dev container

      ./dev.sh stop

- **Attach** the container shell to the terminal so that you can execute commands inside of the container

      ./dev.sh ssh

- Run PHP unit tests inside of the running container
      
      ./dev.sh test

- Execute code check and run tests

      ./dev.sh qc

- Executes composer install --optimize-autoloader

      ./dev.sh install

## <a name="glossary"></a>Glossary

| **Term**    | **Description** |
| :---        | :---        |
| Entity      | Domain object that is modeled and used in the application. It might map to a single row in a relational database, but it won't necessarily do so. |
| (API) Model | Domain representation for specific API. Data-transfer object, POPO that contains only values of attributes and identifiers of related resources. |
| (JSON:API) Resource | Object representation of JSON:API resource defined by the JSON:API specification. |

