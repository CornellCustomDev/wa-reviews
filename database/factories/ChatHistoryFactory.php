<?php

namespace Database\Factories;

use App\Enums\Agents;
use App\Models\Agent;
use App\Models\ChatHistory;
use App\Models\Scope;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ChatHistory>
 */
class ChatHistoryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ulid' => (string) Str::ulid(),
            'agent_id' => fn () => $this->agentId(Agents::GuidelineRecommender),
            'user_id' => User::factory(),
            'context_type' => Scope::class,
            'context_id' => Scope::factory(),
            'name' => 'New chat',
            'messages' => [
                'messages' => [],
                'usage' => null,
                'finish_reason' => 'Stop',
            ],
        ];
    }

    /**
     * A recommender transcript: user prompt, tool calls, then the JSON answer.
     *
     * @param  array<int, int>  $guidelineNumbers
     * @param  array<int, string>  $tools
     */
    public function recommender(array $guidelineNumbers = [1, 2], array $tools = ['scratch_pad'], string $finishReason = 'Stop'): static
    {
        return $this->state(function () use ($guidelineNumbers, $tools, $finishReason) {
            $messages = [
                [
                    'role' => 'user',
                    'content' => [['type' => 'input_text', 'text' => "# Context: Web page scope needing review\nScope details here."]],
                ],
            ];

            foreach ($tools as $index => $tool) {
                $messages[] = [
                    'id' => "fc_$index",
                    'call_id' => "call_$index",
                    'type' => 'function_call',
                    'name' => $tool,
                    'arguments' => '{}',
                ];
                $messages[] = [
                    'type' => 'function_call_output',
                    'call_id' => "call_$index",
                    'output' => 'ok',
                ];
            }

            $messages[] = [
                'role' => 'assistant',
                'content' => [['type' => 'output_text', 'text' => $this->recommendationJson($guidelineNumbers)]],
            ];

            return [
                'agent_id' => $this->agentId(Agents::GuidelineRecommender),
                'messages' => [
                    'messages' => $messages,
                    'usage' => ['promptTokens' => 104750, 'completionTokens' => 1057],
                    'finish_reason' => $finishReason,
                ],
            ];
        });
    }

    /**
     * A StructuredOutput transcript: the `<data>` prompt, the schema as a system
     * message, then the assistant answer twice, as production records it.
     */
    public function structuredOutput(string $data = 'Some unstructured recommendation text.'): static
    {
        return $this->state(function () use ($data) {
            $answer = $this->recommendationJson([4]);

            return [
                'agent_id' => $this->agentId(Agents::StructuredOutput),
                'messages' => [
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => [['type' => 'input_text', 'text' => "<data>\n$data\n</data>"]],
                        ],
                        ['role' => 'system', 'content' => '{"type":"object","properties":{}}'],
                        ['role' => 'assistant', 'content' => [['type' => 'output_text', 'text' => $answer]]],
                        ['role' => 'assistant', 'content' => [['type' => 'output_text', 'text' => $answer]]],
                    ],
                    'usage' => ['promptTokens' => 1400, 'completionTokens' => 320],
                    'finish_reason' => 'Stop',
                ],
            ];
        });
    }

    private function agentId(Agents $agent): int
    {
        return Agent::firstOrCreate(['name' => $agent->value])->id;
    }

    /**
     * @param  array<int, int>  $guidelineNumbers
     */
    private function recommendationJson(array $guidelineNumbers): string
    {
        return json_encode([
            'guidelines' => array_map(fn (int $number) => [
                'number' => $number,
                'reasoning' => 'Because of the missing label.',
            ], $guidelineNumbers),
            'feedback' => 'Overall the page needs label fixes.',
        ]);
    }
}
