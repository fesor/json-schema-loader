<?php

namespace Fesor\JsonSchemaLoader\Apiary;

use Fesor\JsonSchemaLoader\JsonSchemaLoader;
use Fesor\JsonSchemaLoader\SchemaAssembler;

class ApiBlueprintLoader implements JsonSchemaLoader
{
    private $msonTypeExtractor;
    private $parser;

    public function __construct(MsonTypeExtractor $msonTypeExtractor, ApiBlueprintParser $parser)
    {
        $this->msonTypeExtractor = $msonTypeExtractor;
        $this->parser = $parser;
    }

    public function load($url)
    {
        $assembler = new SchemaAssembler($url);
        $content = file_get_contents($url);
        $ast = $this->parser->parse($content);
        $this->msonTypeExtractor->extractTypes($ast, $assembler);

        return $assembler->dump();
    }
}
