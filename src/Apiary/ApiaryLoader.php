<?php

namespace Fesor\JsonSchemaLoader\Apiary;

use Fesor\JsonSchemaLoader\JsonSchemaLoader;
use Fesor\JsonSchemaLoader\SchemaAssembler;
use GuzzleHttp\Client;

class ApiaryLoader implements JsonSchemaLoader
{
    private $client;
    private $extractor;
    private $token;
    /**
     * ApiaryApiBlueprintLoader constructor.
     * @param Client $client
     * @param string $token for Apiary API
     */
    public function __construct(Client $client, ApiBlueprintSchemaExtractor $extractor, $token)
    {
        $this->client = $client;
        $this->extractor = $extractor;
        $this->token = $token;
    }

    public function load($uri)
    {
        $projectName = $uri;
        $assembler = new SchemaAssembler('apiary://' . $projectName);
        $apiblueprint = $this->download($projectName);
        $this->extractor->extractTypes($apiblueprint, $assembler);

        return $assembler->dump();
    }

    private function download($name)
    {
        $res = $this->client->get(
            sprintf('http://api.apiary.io/blueprint/get/%s', $name),
            [
                'headers' => [
                    'Authentication' => sprintf('Token %s', $this->token)
                ]
            ]
        );
        $data = json_decode($res->getBody());
        if ($data->error) {
            // todo: handle errors
        }
        return $data->code;
    }
}
