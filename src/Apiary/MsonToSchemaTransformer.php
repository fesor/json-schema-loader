<?php

namespace Fesor\JsonSchemaLoader\Apiary;

/**
 * Class MsonToSchemaTransformer
 * @package Fesor\JsonSchemaLoader\Apiary
 *
 * A mess of PHP code which generates json schema
 * for MSON elements. Tested on mson-zoo.
 */
class MsonToSchemaTransformer
{
    /** @var  callable ref expander */
    private $refExpander;

    /**
     * Transform MSON element into json schema
     *
     * @param array $mson ast
     * @return array representing json schema
     */
    public function transform(array $mson, callable $refExpander)
    {
        $this->refExpander = $refExpander;
        $schema = [];
        $element = $mson[0];
        if (isset($element['meta']['id'])) {
            $schema['id'] = $element['meta']['id'];
        }

        return array_merge(
            $schema,
            $this->transformElement($element, false)
        );
    }

    private function transformElement(array $element, $isFixed)
    {
        $meta = array_filter([
            'type' => $type = $element['element'],
            'description' => isset($element['meta']['description']) ? $element['meta']['description'] : null,
        ]);

        switch ($type) {
            case 'object':
                $schema = $this->transformObject($element, $isFixed);
                break;
            case 'array':
                $schema = $this->tranformArray($element, $isFixed);
                break;
            case 'enum':
                $schema = $this->transformEnum($element, $isFixed);
                break;
            case 'string':
            case 'number':
            case 'boolean':
                $schema = $this->tranformScalar($element, $isFixed);
                break;
            default:
                $schema = $this->transformRef($element, $this->refExpander);
                break;
        }

        return array_filter(array_merge($meta, $schema), function ($val) {
            return is_array($val) || (bool) $val;
        });
    }

    private function transformObject(array $element, $isFixed)
    {
        $properties = isset($element['content']) ? $element['content'] : [];
        $requiredProperties = array_map(function ($member) {
            return $member['content']['key']['content'];
        }, array_filter(
            $properties,
            function(array $member) {
                return $this->hasTypeAttribute($member, 'fixed') || $this->hasTypeAttribute($member, 'required');
            })
        );

        $schema = [
            'properties' => array_reduce($properties, function ($properties, $member) {
                $properties[$member['content']['key']['content']] = $this->transformElement(
                    $member['content']['value'],
                    $this->hasTypeAttribute($member, 'fixed')
                );

                return $properties;
            }, []),
            'required'  => $requiredProperties,
        ];

        if ($this->hasTypeAttribute($element, 'fixed')) {
            $schema['additionalProperties'] = false;
        }

        return array_filter($schema, function ($field) {
            return !empty($field);
        });
    }

    private function tranformArray(array $element, $isFixed)
    {
        $defaults = null;
        $items = isset($element['content']) ?
            $this->transformArrayItems($element['content'], $isFixed) : null;

        $schema = [];
        if (!empty($element['attributes']['default'])) {
            $schema = $this->handleArrayDefaults($element['attributes']['default']);
        }
        if (!empty($element['attributes']['samples'])) {
            $items = $this->itemsOutOfSamples($this->flat($element['attributes']['samples']));
        }

        return array_filter(array_replace([
            'items' => $items,
            'default' => $defaults
        ], $schema));
    }

    private function flat(array $arr)
    {
        return call_user_func_array('array_merge', $arr);
    }

    private function itemsOutOfSamples(array $samples)
    {
        $types = [];
        foreach ($samples as $sample) {
            $types[] = $sample['element'];
        }
        $types = array_unique($types);

        if (count($types) === 1) {
            return [
                'type' => reset($types)
            ];
        }

        return [
            'onOf' => array_map(function ($type) {
                return ['type' => $type];
            }, $types)
        ];
    }

    private function handleArrayDefaults(array $defaults)
    {
        $schemaDefault = [];
        foreach ($defaults as $default) {
            $schemaDefault[] = $default['content'];
        }

        return [
            'default' => $schemaDefault,
            'items' => $this->itemsOutOfSamples($defaults)
        ];
    }

    private function transformArrayItems(array $items, $isFixed)
    {
        if ($isFixed) {
            return $this->transformFixedArrayItems($items);
        }

        $nonSamplesItems = array_values(array_filter($items, function ($item) {
            return !isset($item['attributes']['samples']);
        }));

        if (count($nonSamplesItems) === 0) {
            return null;
        }

        if (count($nonSamplesItems) === 1) {
            return $this->transformElement($nonSamplesItems[0], $isFixed);
        }

        $types = [];
        foreach ($nonSamplesItems as $item) {
            $types[$item['element']] = $this->transformElement($item, false);
        }

        if (count($types) === 1) {
            return reset($types);
        }

        return [
            'oneOf' => array_values($types)
        ];
    }

    private function transformFixedArrayItems(array $items)
    {
        return array_map(function ($item) {
            return $this->transformElement($item, true);
        }, $items);
    }

    private function tranformScalar(array $element, $isFixed)
    {
        $schema = [];

        if (isset($element['attributes']['default'])) {
            $schema['default'] = $element['attributes']['default'];
        }

        if ($isFixed && isset($element['content'])) {
            $schema['enum'] = [$element['content']];
        }

        return $schema;
    }

    private function transformRef(array $element, callable $refExpander)
    {
        return [
            'type' => null,
            '$ref' => $refExpander($element['element'])
        ];
    }

    private function transformEnum(array $element, $isFixed)
    {
        $content = isset($element['content']) ? $element['content'] : [];
        $values = [];
        foreach ($content as $value) {
            $sample = $this->sample($value);
            if ($value['element'] === 'enum') {
                $values = array_merge($values, $sample);
            } else {
                $values[] = $sample;
            }
        }

        return [
            'type' => null,
            'enum' => array_reduce($values, function ($enum, $value) {
                if (!in_array($value, $enum, true)) $enum[] = $value;

                return $enum;
            }, []),
            'default' => !empty($element['attributes']['default']) ?
                $this->sample($element['attributes']['default'][0]) : null,
        ];
    }

    private function sample($sample)
    {
        switch($sample['element']) {
            case 'string':
            case 'number':
            case 'boolean':

                return isset($sample['content']) ? $sample['content'] : null;
            case 'array':

                return array_map(function ($item) {
                    return $this->sample($item);
                }, isset($sample['content']) ? $sample['content'] : []);
            case 'object':
                $object = [];
                $content = isset($sample['content']) ? $sample['content'] : [];
                foreach ($content as $member) {
                    $value = $this->sample($member['content']['value']);
                    $object[$member['content']['key']['content']] = $value;
                }

                return (object) $object;
            case 'enum':
                $enum = $this->transformEnum($sample, false);

                return $enum['enum'];
        }

        return [];
    }

    private function hasTypeAttribute($element, $name)
    {
        if (!isset($element['attributes']['typeAttributes'])) {
            return false;
        }

        return in_array($name, $element['attributes']['typeAttributes']);
    }
}