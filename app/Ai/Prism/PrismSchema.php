<?php

namespace App\Ai\Prism;

use Prism\Prism\Contracts\Schema;
use Prism\Prism\Schema\ArraySchema;
use Prism\Prism\Schema\BooleanSchema;
use Prism\Prism\Schema\EnumSchema;
use Prism\Prism\Schema\NumberSchema;
use Prism\Prism\Schema\ObjectSchema;
use Prism\Prism\Schema\StringSchema;

trait PrismSchema
{
    private function convertToPrismSchema(array $schema): Schema
    {
        $type = $schema['type'] ?? null;

        if ($type === 'object') {
            $properties = [];

            foreach ($schema['properties'] as $propName => $propSchema) {
                $properties[] = $this->convertToPrismSchema(array_merge($propSchema, ['name' => $propName]));
            }

            return new ObjectSchema(
                name: $schema['name'] ?? 'object',
                description: $schema['description'] ?? '',
                properties: $properties,
                requiredFields: $schema['required'] ?? [],
                allowAdditionalProperties: $schema['additionalProperties'] ?? false
            );
        }

        if ($type === 'array') {
            return new ArraySchema(
                name: $schema['name'] ?? 'array',
                description: $schema['description'] ?? '',
                items: $this->convertToPrismSchema($schema['items'])
            );
        }

        if ($type === 'string') {
            if (isset($schema['enum'])) {
                return new EnumSchema(
                    name: $schema['name'] ?? 'enum',
                    description: $schema['description'] ?? '',
                    options: $schema['enum']
                );
            }

            return new StringSchema(
                name: $schema['name'] ?? 'string',
                description: $schema['description'] ?? ''
            );
        }

        if ($type === 'integer' || $type === 'number') {
            return new NumberSchema(
                name: $schema['name'] ?? 'number',
                description: $schema['description'] ?? ''
            );
        }

        if ($type === 'boolean') {
            return new BooleanSchema(
                name: $schema['name'] ?? 'boolean',
                description: $schema['description'] ?? ''
            );
        }

        // Default to string if type is not recognized
        return new StringSchema(
            name: $schema['name'] ?? 'unknown',
            description: $schema['description'] ?? ''
        );
    }
}
