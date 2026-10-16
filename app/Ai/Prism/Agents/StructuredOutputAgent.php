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
use Prism\Prism\ValueObjects\Messages\AssistantMessage;

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

If the data requires transformation, stay as true to the original data as possible while adhering to the schema provided. Copy free-text fields (such as reasoning, observation, recommendation, testing, and feedback) verbatim from the source data — do not rephrase, summarize, or "improve" the wording, even if it seems terse or informal.

If a field expects an array and there is nothing to report, use an empty array. Never use null for an array field.

If an item in the data puts several values in a field that holds only one, such as "number": "33 and 34", output a separate item for each value, in the order given, copying every other field of the item verbatim into each one. Never keep only the first value.
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
        // Structured responses have no messages collection, so the conversation
        // comes from the final step, with the assistant response appended
        $messages = $response->steps->last()?->messages ?? [];
        $messages[] = new AssistantMessage($response->text);

        return (new MessageMap($messages, []))();
    }
}
