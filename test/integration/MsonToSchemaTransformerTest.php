<?php

use \Fesor\JsonSchemaLoader\Apiary\MsonToSchemaTransformer;

class MsonToSchemaTransformerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var MsonToSchemaTransformer
     */
    private $transformer;

    public function setUp()
    {
        $this->transformer = new MsonToSchemaTransformer();
    }

    /**
     * @dataProvider msonProvider
     */
    public function testMsonToSchemaTranformer($jsonSchema, $msonParsed, $fileName)
    {
        $transformedShema = $this->transformer->transform($msonParsed, function () {});

        $this->assertEquals(
            $jsonSchema,
            json_encode($transformedShema, JSON_PRETTY_PRINT),
            sprintf('Incorrect result for "%s"', $fileName)
        );
    }

    public function msonProvider()
    {
        $files = glob(__DIR__ . '/../support/mson-schemas/*.json');

        return array_map(function ($jsonSchemaFile) {
            $fileName = pathinfo($jsonSchemaFile, PATHINFO_BASENAME);
            $jsonSchema = file_get_contents($jsonSchemaFile);
            $parsedMson = json_decode(
                file_get_contents(__DIR__ . '/../../vendor/apiary/mson-zoo/samples-parsed/' . $fileName),
                true
            );

            return [
                json_encode(json_decode($jsonSchema), JSON_PRETTY_PRINT),
                $parsedMson,
                $fileName
            ];
        }, $files);
    }
}