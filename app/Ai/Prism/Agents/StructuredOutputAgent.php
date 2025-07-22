<?php

namespace App\Ai\Prism\Agents;

use App\Enums\ChatProfile;
use Prism\Prism\Contracts\Schema;
use Prism\Prism\Structured\PendingRequest;
use Prism\Prism\Structured\Response;

class StructuredOutputAgent extends PendingRequest
{
    public function __construct(
        Schema $schema,
        string $data,
    ) {
        $this->using(
            provider: config('cornell_ai.prism_provider'),
            model: config('cornell_ai.profiles')[ChatProfile::Task->value]['model'],
        );

        $this->withSystemPrompt($this->instructions());
        $this->withSchema($schema);
        $this->withProviderOptions([
            'schema' => ['strict' => true],
        ]);
        $this->withPrompt("<data>\n$data\n</data>");
    }

    public static function for(Schema $schema, string $data): static
    {
        return new static($schema, $data);
    }

    public function getResponse(): Response
    {
        return $this->asStructured();
    }

    protected function instructions(): string
    {
        return <<<INSTRUCTIONS
You are a structured output agent. Your task is to format the data the user provides in <data>.

The data may already be in the format you need, or it may require some transformation.

If the data requires transformation, stay as true to the original data as possible while adhering to the schema provided.
INSTRUCTIONS;
    }
}
