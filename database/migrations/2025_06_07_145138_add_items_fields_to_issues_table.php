<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('issues', function (Blueprint $table) {
            // Add fields from items table (if not already present)
            $table->foreignId('guideline_id')->nullable()->after('scope_id')->constrained();
            $table->string('assessment', 20)->nullable()->after('sia_rule_id');
            $table->string('testing_method', 255)->nullable()->after('description');
            $table->text('testing')->nullable()->after('recommendation');
            $table->longText('image_links')->nullable()->after('testing');
            $table->string('impact', 20)->nullable()->after('content_issue');
            $table->text('ai_reasoning')->nullable()->after('impact');
            $table->string('ai_status', 255)->nullable()->after('ai_reasoning');
            $table->foreignId('agent_id')->nullable()->after('ai_status')->constrained();
        });

        // Migrate existing data from items table to issues table
        $itemsByIssue = DB::table('items')->get()->groupBy('issue_id');
        foreach ($itemsByIssue as $issueId => $items) {
            $originalContent = (array) DB::table('issues')->find($issueId);
            $firstItem = true;

            foreach ($items as $item) {
                $itemData = $this->mergeItemData($originalContent, $item);

                if ($item->deleted_at && empty($originalContent['deleted_at'])) {
                    $this->cloneIssue($originalContent, $itemData);
                    continue;
                }

                if ($firstItem) {
                    DB::table('issues')->where('id', $issueId)->update($itemData);
                    $firstItem = false;
                } else {
                    $this->cloneIssue($originalContent, $itemData);
                }
            }
        }
    }

    private function mergeItemData(array $issueContent, $item): array
    {
        return [
            'guideline_id'   => $item->guideline_id,
            'assessment'     => $item->assessment,
            'testing_method' => $item->testing_method,
            'testing'        => $item->testing,
            'image_links'    => $item->image_links,
            'impact'         => $item->impact,
            'ai_reasoning'   => $item->ai_reasoning,
            'ai_status'      => $item->ai_status,
            'agent_id'       => $item->agent_id,
            'description'    => $item->description ?: $issueContent['description'] ?? null,
            'recommendation' => $item->recommendation,
            'content_issue'  => $item->content_issue,
            'created_at'     => $item->created_at,
            'updated_at'     => $item->updated_at,
            'deleted_at'     => $item->deleted_at,
        ];
    }

    private function cloneIssue(array $originalContent, array $overrideData = []): void
    {
        $content = array_merge($originalContent, $overrideData);
        unset($content['id']);
        DB::table('issues')->insert($content);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('issues', function (Blueprint $table) {
            $table->dropForeign(['guideline_id']);
            $table->dropColumn([
                'guideline_id',
                'assessment',
                'testing_method',
                'testing',
                'image_links',
                'impact',
                'ai_reasoning',
                'ai_status',
                'agent_id',
            ]);
        });
    }
};
