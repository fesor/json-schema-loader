<?php

namespace Fesor\JsonSchemaLoader\Apiary\Parser;

use Fesor\JsonSchemaLoader\Apiary\ApiBlueprintParser;
use GuzzleHttp\Client;

class RemoteApiBlueprintParser implements ApiBlueprintParser
{
    private $client;

    /**
     * ApiBlueprintDataStructureExtractor constructor.
     * @param $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function parse(string $apiBlueprint)
    {
        $res = $this->client->post('https://api.apiblueprint.org/parser', [
            'body' => $apiBlueprint,
            'headers' => [
                'Content-Type' => 'text/vnd.apiblueprint',
                'Accept' => 'application/vnd.apiblueprint.parseresult+json'
            ]
        ]);

        $json = $res->getBody()->getContents();

        $data = json_decode($json, true);
        if (JSON_ERROR_NONE !== json_last_error()) {
            // todo: handle errors
        }

        return $data;
    }
}
