<?php

namespace Fesor\JsonSchemaLoader;

class SchemaAssembler
{
    private $id;
    private $definitions;

    /**
     * SchemaStorage constructor.
     * @param $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    public function add($schema)
    {
        $this->definitions[$schema['id']] = $schema;
    }

    public function dump()
    {
        return json_encode([
            'id' => $this->id,
            'definitions' => $this->definitions
        ]);
    }

    public function createRefExpander() : callable
    {
        return function ($id) {
            return $this->id . '#definitions/' . $id;
        };
    }
}
