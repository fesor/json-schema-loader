<?php

namespace Fesor\JsonSchemaLoader\Bridge\JsonSchema;

use Fesor\JsonSchemaLoader\JsonSchemaLoader;
use JsonSchema\Uri\Retrievers\UriRetrieverInterface;
use JsonSchema\Uri\UriResolver;

class JsonSchemaRetriever implements UriRetrieverInterface
{
    /**
     * @var UriRetrieverInterface
     */
    private $baseRetriever;
    /**
     * @var JsonSchemaLoader[]
     */
    private $loadersBySchema;
    /**
     * @var JsonSchemaLoader[]
     */
    private $loadersByExtension;

    public function __construct(UriRetrieverInterface $baseRetriever)
    {
        $this->baseRetriever = $baseRetriever;
        $this->loadersBySchema = $this->loadersByExtension = [];
    }

    public function registerLoaderForSchema(JsonSchemaLoader $loader, $schema)
    {
        $this->loadersBySchema[$schema] = $loader;
    }

    public function registerLoaderForExtension(JsonSchemaLoader $loader, $extension)
    {
        $this->loadersByExtension[$extension] = $loader;
    }

    public function retrieve($uri)
    {
        $resolver = new UriResolver();
        $parsed = $resolver->parse($uri);
        if (isset($this->loadersBySchema[$parsed['scheme']])) {

            return $this->loadersBySchema[$parsed['scheme']]->load($parsed['path']);
        }

        $extension = pathinfo($parsed['path'], PATHINFO_EXTENSION);
        if (isset($this->loadersByExtension[$extension])) {
            return $this->loadersByExtension[$extension]->load($parsed['path']);
        }

        return $this->baseRetriever->retrieve($uri);
    }

    public function getContentType()
    {
        return null;
    }
}