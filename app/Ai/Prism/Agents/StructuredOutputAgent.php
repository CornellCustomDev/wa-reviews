<?php

namespace App\Ai\Prism\Agents;

use App\Ai\Prism\PrismHistory;
use App\Enums\Agents;
use App\Enums\ChatProfile;
use App\Models\Agent;
use Illuminate\Database\Eloquent\Model;
use Prism\Prism\Contracts\Schema;
use Prism\Prism\Providers\OpenAI\Maps\MessageMap;
use Prism\Prism\Structured\PendingRequest;
use Prism\Prism\Structured\Response;

class StructuredOutputAgent extends PendingRequest
{
    use PrismHistory;

    protected Agent $agent;

    public function __construct(
        Schema $schema,
        string $data,
        protected ?Model $contextModel,
    ) {
        $this->agent = Agent::findAgent(Agents::StructuredOutput);

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

    public static function for(Schema $schema, string $data, ?Model $contextModel = null): static
    {
        return new static($schema, $data, $contextModel);
    }

    private function instructions(): string
    {
        return <<<INSTRUCTIONS
You are a structured output agent. Your task is to format the data the user provides in <data>.

The data may already be in the format you need, or it may require some transformation.

If the data requires transformation, stay as true to the original data as possible while adhering to the schema provided.
INSTRUCTIONS;
    }

    public function getResponse(): Response
    {
        $response = $this->asStructured();

        $this->storeChatHistory(
            agent: $this->agent,
            contextModel: $this->contextModel,
            response: $response,
            messages: $this->mapMessages($response)
        );

        return $response;
    }

    private function mapMessages(Response $response): array
    {
        return (new MessageMap($response->responseMessages->toArray(), []))();
    }
}
