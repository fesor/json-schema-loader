<?php

namespace Fesor\JsonSchemaLoader\Apiary;

use Fesor\JsonSchemaLoader\SchemaAssembler;

class MsonTypeExtractor
{
    private $schemaTransformer;

    /**
     * MsonTypeExtractor constructor.
     * @param MsonToSchemaTransformer $schemaTransformer
     */
    public function __construct(MsonToSchemaTransformer $schemaTransformer)
    {
        $this->schemaTransformer = $schemaTransformer;
    }

    public function extractTypes(array $ast, SchemaAssembler $assembler)
    {
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
