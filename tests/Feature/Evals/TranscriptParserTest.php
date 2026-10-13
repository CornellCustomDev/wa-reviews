<?php

namespace Tests\Feature\Evals;

use App\Services\Evals\RecommendationParser;
use App\Services\Evals\TranscriptParser;
use Tests\TestCase;

class TranscriptParserTest extends TestCase
{
    public function test_it_reads_the_prompt_answer_and_tool_calls_from_a_transcript(): void
    {
        $parser = new TranscriptParser([
            'messages' => [
                ['role' => 'user', 'content' => [['type' => 'input_text', 'text' => 'Review this scope.']]],
                ['type' => 'function_call', 'call_id' => 'c1', 'name' => 'scratch_pad', 'arguments' => '{"note":"a"}'],
                ['type' => 'function_call_output', 'call_id' => 'c1', 'output' => 'stored'],
                ['type' => 'function_call', 'call_id' => 'c2', 'name' => 'scratch_pad', 'arguments' => '{"note":"b"}'],
                ['type' => 'function_call_output', 'call_id' => 'c2', 'output' => 'stored'],
                ['role' => 'assistant', 'content' => [['type' => 'output_text', 'text' => 'Answer.']]],
            ],
            'usage' => ['promptTokens' => 100, 'completionTokens' => 20],
            'finish_reason' => 'Stop',
        ]);

        $this->assertSame('Review this scope.', $parser->firstUserText());
        $this->assertSame('Answer.', $parser->lastAssistantText());
        $this->assertSame('Stop', $parser->finishReason());
        $this->assertSame(100, $parser->usage()['promptTokens']);
        $this->assertCount(2, $parser->toolCalls());
        $this->assertSame(['scratch_pad'], $parser->toolNames());
    }

    public function test_it_takes_the_last_assistant_turn_when_the_answer_is_duplicated(): void
    {
        $parser = new TranscriptParser([
            'messages' => [
                ['role' => 'user', 'content' => [['type' => 'input_text', 'text' => "<data>\nraw\n</data>"]]],
                ['role' => 'system', 'content' => '{"type":"object"}'],
                ['role' => 'assistant', 'content' => [['type' => 'output_text', 'text' => 'partial']]],
                ['role' => 'assistant', 'content' => [['type' => 'output_text', 'text' => 'complete']]],
            ],
        ]);

        $this->assertSame('complete', $parser->lastAssistantText());
        $this->assertNull($parser->finishReason());
        $this->assertNull($parser->usage());
    }

    public function test_it_handles_an_empty_transcript(): void
    {
        $parser = new TranscriptParser([]);

        $this->assertNull($parser->firstUserText());
        $this->assertNull($parser->lastAssistantText());
        $this->assertSame([], $parser->toolCalls());
    }

    public function test_recommendation_parser_reads_plain_json(): void
    {
        $parsed = RecommendationParser::parse(json_encode([
            'guidelines' => [['number' => 7], ['number' => '3']],
            'feedback' => 'Fix the labels.',
        ]));

        $this->assertSame('json', $parsed['parse_method']);
        $this->assertSame([3, 7], $parsed['guideline_ids']);
        $this->assertSame('Fix the labels.', $parsed['feedback']);
        $this->assertCount(2, $parsed['guidelines']);
    }

    public function test_recommendation_parser_reads_fenced_json(): void
    {
        $text = "Here you go:\n```json\n{\"guidelines\":[{\"number\":12}],\"feedback\":\"ok\"}\n```";

        $parsed = RecommendationParser::parse($text);

        $this->assertSame('json', $parsed['parse_method']);
        $this->assertSame([12], $parsed['guideline_ids']);
    }

    public function test_recommendation_parser_falls_back_to_scraping_ids(): void
    {
        $parsed = RecommendationParser::parse('Truncated... "number": 5, "number": "5", "number": 0');

        $this->assertSame('regex', $parsed['parse_method']);
        $this->assertSame([5], $parsed['guideline_ids']);
        $this->assertSame([], $parsed['guidelines']);
    }

    public function test_recommendation_parser_recovers_ids_packed_into_one_number_field(): void
    {
        $parsed = RecommendationParser::parse(json_encode([
            'guidelines' => [['number' => '35, 36'], ['number' => 14]],
        ]));

        $this->assertSame(
            [14, 35, 36],
            $parsed['guideline_ids'],
            'Production drops a non-numeric number outright; the intent is still recoverable.'
        );
        $this->assertSame(['35, 36'], $parsed['malformed_numbers']);
    }

    public function test_recommendation_parser_reports_empty_text(): void
    {
        $this->assertSame('empty', RecommendationParser::parse(null)['parse_method']);
        $this->assertSame([], RecommendationParser::guidelineIds(''));
    }
}
