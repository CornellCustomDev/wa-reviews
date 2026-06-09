<?php

namespace App\Models;

use App\Enums\IssueStatus;
use App\Enums\ReportType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'type',
        'completed_at',
        'completed_by',
        'urls_included',
        'urls_excluded',
        'review_procedure',
        'summary',
    ];

    protected $casts = [
        'type' => ReportType::class,
        'completed_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function issues(): BelongsToMany
    {
        return $this->belongsToMany(Issue::class, 'report_issues')
            ->withPivot(['status']);
    }

    public function reportableIssues(): Collection
    {
        $query = $this->completed_at
            ? $this->issues()
            : $this->project->issues()->isReportable();

        return $query
            ->with(['scope', 'guideline:id,number,name,criterion_id', 'guideline.criterion:id,number,name,level'])
            ->get()
            ->sort(fn($a, $b) => $a->guideline_id <=> $b->guideline_id);
    }

    public function isReady(): bool
    {
        return ! empty($this->summary);
    }

    public function isCompleted(): bool
    {
        return ! empty($this->completed_at);
    }

    /**
     * Associate all unreported issues with this report and mark them as reviewed
     */
    public function addIssuesToReport(): void
    {
        if ($this->completed_at) {
            return;
        }

        $reportableIssues = $this->reportableIssues();

        // Mark any new issues as first reviewed on this report
        Issue::whereIn('id', $reportableIssues->pluck('id'))
            ->whereNull('report_id')
            ->update([
                'report_id' => $this->id,
            ]);

        // Mark any issues without a review status as reviewed
        Issue::whereIn('id', $reportableIssues->pluck('id'))
            ->whereNull('status')
            ->update([
                'status' => IssueStatus::Reviewed,
            ]);

        // Now return all the current values
        $reportIssues = $reportableIssues->fresh()->where('report_id', $this->id);

        // Sync the issues to the report->issues pivot table, with the status of each issue
        $reportIssueUpdates = $reportIssues->mapWithKeys(fn(Issue $issue) => [
            $issue->id => ['status' => $issue->status->value]
        ]);
        $this->issues()->sync($reportIssueUpdates);
    }

    public function completeReport(): void
    {
        $this->addIssuesToReport();
        $this->update([
            'completed_by' => auth()->id(),
            'completed_at' => now(),
        ]);
    }

    public function rollbackReport(): void
    {
        if (! $this->completed_at) {
            return;
        }

        // Detach all issues from the report
        $this->issues()->detach();

        // Unset all issues that were first reviewed on this report
        Issue::where('report_id', $this->id)
            ->update([
                'report_id' => null,
                'status' => null,
            ]);

        $this->update([
            'completed_by' => null,
            'completed_at' => null,
        ]);
    }
}
