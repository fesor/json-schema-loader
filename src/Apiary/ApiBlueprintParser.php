<?php

namespace Fesor\JsonSchemaLoader\Apiary;

interface ApiBlueprintParser
{
    public function parse(string $apiBlueprint);
}