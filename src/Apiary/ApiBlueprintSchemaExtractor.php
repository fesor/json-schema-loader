<?php

namespace Fesor\JsonSchemaLoader\Apiary;

use Fesor\JsonSchemaLoader\SchemaAssembler;

class ApiBlueprintSchemaExtractor
{
    private $schemaTransformer;

    private $parser;

    /**
     * MsonTypeExtractor constructor.
     * @param MsonToSchemaTransformer $schemaTransformer
     * @param ApiBlueprintParser $parser
     */
    public function __construct(MsonToSchemaTransformer $schemaTransformer, ApiBlueprintParser $parser)
    {
        $this->schemaTransformer = $schemaTransformer;
        $this->parser = $parser;
    }

    public function extractTypes($apiBlueprint, SchemaAssembler $assembler)
    {
        $ast = $this->parser->parse($apiBlueprint);
        $dataStructures = $this->collectDataStructures($ast['ast']);
        $this->processDataStructures($dataStructures, $assembler);
    }

    private function processDataStructures(array $dataStructures, SchemaAssembler $assembler)
    {
        foreach ($dataStructures as $dataStructure) {
            $schema = $this->schemaTransformer->transform($dataStructure, $assembler->createRefExpander());
            $assembler->add($schema);
        }
    }

    private function collectDataStructures(array $ast)
    {
        $dataStructures = [];
        foreach ($ast['content'] as $category) {
            foreach ($category['content'] as $dataStructure) {
                if ('dataStructure' !== $dataStructure['element']) {
                    continue;
                }

                $dataStructures[] = $dataStructure['content'];
            }
        }

        return $dataStructures;
    }
}
