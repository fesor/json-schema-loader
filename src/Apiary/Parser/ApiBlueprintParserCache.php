<?php

namespace Fesor\JsonSchemaLoader\Apiary\Parser;

use Fesor\JsonSchemaLoader\Apiary\ApiBlueprintParser;

class ApiBlueprintParserCache implements ApiBlueprintParser
{
    private $cacheDir;

    private $parser;

    public function __construct($cacheDir, ApiBlueprintParser $parser)
    {
        $this->cacheDir = $cacheDir;
        $this->parser = $parser;
    }

    public function parse(string $apiBlueprint)
    {
        $cachedFile = $this->cacheDir . '/' . md5($apiBlueprint) . '.php';
        if (is_file($cachedFile)) {
            return include $cachedFile;
        }

        $result = $this->parser->parse($apiBlueprint);
        file_put_contents($cachedFile, '<?php return ' . var_export($result, true) . ';');

        return $result;
    }
}