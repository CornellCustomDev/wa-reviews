<?php

namespace Tests\Integration\Prism;

use App\Ai\Prism\Agents\StructuredOutputAgent;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Schema\StringSchema;
use Prism\Prism\Testing\StructuredResponseFake;
use Prism\Prism\Testing\StructuredStepFake;
use Prism\Prism\ValueObjects\Messages\UserMessage;
use Tests\TestCase;

class StructuredOutputAgentTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_stores_the_conversation_in_the_chat_history()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Prism::fake([
            StructuredResponseFake::make()
                ->withText('{"answer":"blue"}')
                ->withSteps(collect([
                    StructuredStepFake::make()
                        ->withMessages([new UserMessage('<data>The sky is blue</data>')]),
                ])),
        ]);

        $agent = StructuredOutputAgent::for(
            schema: new StringSchema('answer', 'The answer'),
            data: 'The sky is blue',
            contextModel: $user,
        );

        $response = $agent->getResponse();

        $this->assertSame('{"answer":"blue"}', $response->text);

        // Structured responses no longer carry a messages collection, so the
        // history is assembled from the final step plus the assistant response
        $messages = $agent->getChatHistory()->messages['messages'];

        $this->assertSame(['user', 'assistant'], array_column($messages, 'role'));
    }
}
