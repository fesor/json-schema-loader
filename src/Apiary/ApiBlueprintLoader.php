<?php

namespace Fesor\JsonSchemaLoader\Apiary;

use Fesor\JsonSchemaLoader\JsonSchemaLoader;
use Fesor\JsonSchemaLoader\SchemaAssembler;

class ApiBlueprintLoader implements JsonSchemaLoader
{
    private $msonTypeExtractor;

    public function __construct(ApiBlueprintSchemaExtractor $msonTypeExtractor)
    {
        $this->msonTypeExtractor = $msonTypeExtractor;
    }

    public function load($url)
    {
        $assembler = new SchemaAssembler($url);
        $content = file_get_contents($url);
        $this->msonTypeExtractor->extractTypes($content, $assembler);

        return $assembler->dump();
    }
}
