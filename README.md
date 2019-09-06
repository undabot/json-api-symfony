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