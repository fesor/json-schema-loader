Json Schema Loader
=========================

Some developers prefer to write their API documentation in Apiary, RAML or some other documentation format. This often
brings the problem of synchronization of documentation and implementation. To help with this problem, this package 
provides you set of custom schema loaders which supports schema retrieval dirrectly from your api documentation.

Until release of 1.0 version only ApiBlueprint and Apiary will be supported, but new documentation formats will be
available in later releases.

**Note**: This package in heavily development phase.

## Usage

This library currently provides bridge only for [justinrainbow/json-schema](https://github.com/justinrainbow/json-schema).

To use schemas from different sources just specify custom retriaver:

```php
$retriaver = new \JsonSchema\Uri\UriRetriever();
$retriaver->setUriRetriever($loader);
# Load json schema from api blueprint file
$schema = $retriaver->retrieve('file://'.__DIR__.'/example.apib#definitions/SomeDataStructure');
# Or dirrectly from apiary
$schema = $retriaver->retrieve('apiary://example-project#definitions/SomeDataStructure');
```
