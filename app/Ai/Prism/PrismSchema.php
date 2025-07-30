<?php

namespace App\Ai\Prism;

use App\Ai\Prism\Agents\StructuredOutputAgent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use JsonException;
use Prism\Prism\Contracts\Schema;
use Prism\Prism\Schema\ArraySchema;
use Prism\Prism\Schema\BooleanSchema;
use Prism\Prism\Schema\EnumSchema;
use Prism\Prism\Schema\NumberSchema;
use Prism\Prism\Schema\ObjectSchema;
use Prism\Prism\Schema\StringSchema;
use Prism\Prism\Structured\Response as StructuredResponse;
use Prism\Prism\Text\Response as TextResponse;
use UnexpectedValueException;

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

    /**
     * @throws UnexpectedValueException
     */
    protected function getStructuredResponse(
        TextResponse|StructuredResponse $prismResponse,
        Schema $schema,
        ?Model $contextModel = null,
    ): mixed
    {
        // If $data is already a valid JSON string, presume it is structured already
        if ($response = json_decode($prismResponse->text)) {
            return $response;
        }

        $agent = StructuredOutputAgent::for(
            schema: $schema,
            data: $prismResponse->text,
            contextModel: $contextModel,
        );

        $structuredResponse = $agent->getResponse()->text;

        try {
            return json_decode($structuredResponse, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            Log::error('getStructuredResponse: JSON decode error', [
                'error' => $e->getMessage(),
                'response' => $structuredResponse,
                'trace' => $e->getTraceAsString(),
            ]);

            throw new UnexpectedValueException('Error processing structured response: ' . $e->getMessage(), 0, $e);
        }
    }
}
