<?php

namespace Tests\Feature\Evals;

use App\Ai\Prism\Tools\ScratchPadTool;
use App\Services\Evals\ReplayScratchPadTool;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\FeatureTestCase;

class ReplayScratchPadToolTest extends FeatureTestCase
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function actions(): array
    {
        return [
            'store' => ['store', '{"status":"success","message":"Data appended to scratch pad."}'],
            'append' => ['append', '{"status":"success","message":"Data appended to scratch pad."}'],
            'unknown action' => ['clear', '{"error":"invalid_action"}'],
        ];
    }

    #[DataProvider('actions')]
    public function test_it_answers_as_production_does_without_writing_the_scratch_pad(string $action, string $expected): void
    {
        Storage::fake();

        $replayResponse = (new ReplayScratchPadTool)($action, 'Plan: check the images.');

        $this->assertSame($expected, $replayResponse);
        Storage::assertMissing('scratch_pad.txt');

        $this->assertSame($expected, (new ScratchPadTool)($action, 'Plan: check the images.'), 'Production still answers the same way.');
    }

    public function test_it_keeps_the_name_the_model_calls(): void
    {
        $this->assertSame('scratch_pad', (new ReplayScratchPadTool)->name());
    }
}
