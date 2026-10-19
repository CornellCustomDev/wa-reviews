<?php

namespace App\Services\Evals;

use App\Enums\AIStatus;
use App\Models\ChatHistory;
use App\Models\Issue;
use App\Models\Item;
use App\Models\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Recovers reviewer verdicts for a historical recommender run.
 *
 * `ScopeAnalyzer` writes one Issue per recommended guideline and `IssueAnalyzer`
 * writes one Item, both stamped with the run's `chat_history_ulid`. Reviewers
 * then triage each row through `ai_status`, which is the only human signal in
 * the system about whether a recommendation was any good.
 */
class LabelMiner
{
    /**
     * @return array{
     *     source: string,
     *     produced_guideline_ids: array<int, int>,
     *     accepted_guideline_ids: array<int, int>,
     *     modified_guideline_ids: array<int, int>,
     *     rejected_guideline_ids: array<int, int>,
     *     untriaged_guideline_ids: array<int, int>,
     *     missed_guideline_ids: array<int, int>,
     *     pre_existing_guideline_ids: array<int, int>,
     *     triaged_count: int,
     * }|null
     */
    public function mine(ChatHistory $history): ?array
    {
        $isIssueContext = $history->context_type === Issue::class;

        $rows = $isIssueContext
            ? Item::query()->where('chat_history_ulid', $history->ulid)
            : Issue::query()->where('chat_history_ulid', $history->ulid);

        $produced = $rows->clone()->whereNotNull('guideline_id')->get(['guideline_id', 'ai_status']);

        if ($produced->isEmpty()) {
            return null;
        }

        $byStatus = fn (?AIStatus $status) => $this->ids(
            $produced->filter(fn ($row) => $this->status($row) === $status)
        );

        $accepted = $byStatus(AIStatus::Accepted);
        $modified = $byStatus(AIStatus::Modified);
        $rejected = $byStatus(AIStatus::Rejected);

        return [
            'source' => $isIssueContext ? 'items' : 'issues',
            'produced_guideline_ids' => $this->ids($produced),
            'accepted_guideline_ids' => $accepted,
            'modified_guideline_ids' => $modified,
            'rejected_guideline_ids' => $rejected,
            'untriaged_guideline_ids' => $byStatus(AIStatus::Generated),
            'missed_guideline_ids' => $this->minedMisses($history, $isIssueContext),
            'pre_existing_guideline_ids' => $this->preExisting($history, $isIssueContext),
            'triaged_count' => count($accepted) + count($modified) + count($rejected),
        ];
    }

    /**
     * Guidelines a human added to the same scope or issue after the run, which
     * the model did not surface. Approximate recall signal only: a reviewer may
     * have added them for reasons unrelated to the AI pass.
     *
     * @return array<int, int>
     */
    private function minedMisses(ChatHistory $history, bool $isIssueContext): array
    {
        $siblings = $this->siblingRows($history, $isIssueContext);

        if (! $siblings) {
            return [];
        }

        return $this->ids(
            $siblings->whereNull('ai_status')
                ->where('created_at', '>', $history->created_at)
                ->get(['guideline_id'])
        );
    }

    /**
     * Guidelines already recorded before the run. `ScopeAnalyzer` silently skips
     * these when storing, so the model may have returned them without leaving a
     * row behind — they must not count against precision.
     *
     * @return array<int, int>
     */
    private function preExisting(ChatHistory $history, bool $isIssueContext): array
    {
        $siblings = $this->siblingRows($history, $isIssueContext);

        if (! $siblings) {
            return [];
        }

        return $this->ids(
            $siblings->where('created_at', '<', $history->created_at)
                ->where(fn ($query) => $query->whereNull('chat_history_ulid')
                    ->orWhere('chat_history_ulid', '!=', $history->ulid))
                ->get(['guideline_id'])
        );
    }

    private function siblingRows(ChatHistory $history, bool $isIssueContext): ?Builder
    {
        if (! $history->context_id) {
            return null;
        }

        if ($isIssueContext) {
            return Item::query()
                ->where('issue_id', $history->context_id)
                ->whereNotNull('guideline_id');
        }

        if ($history->context_type !== Scope::class) {
            return null;
        }

        return Issue::query()
            ->where('scope_id', $history->context_id)
            ->whereNotNull('guideline_id');
    }

    /**
     * @param  Collection<int, Issue|Item>  $rows
     * @return array<int, int>
     */
    private function ids($rows): array
    {
        $ids = $rows->pluck('guideline_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->sort()
            ->values()
            ->all();

        return $ids;
    }

    private function status(Issue|Item $row): ?AIStatus
    {
        $status = $row->ai_status;

        if ($status instanceof AIStatus) {
            return $status;
        }

        return is_string($status) ? AIStatus::tryFrom($status) : null;
    }
}
