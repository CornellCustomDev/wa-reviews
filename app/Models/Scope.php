<?php

namespace App\Models;

use App\Enums\Agents;
use App\Enums\GuidelineStatus;
use App\Services\SiteImprove\SiteimproveService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Log;

class Scope extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'title',
        'url',
        'current_page_id',
        'notes',
        'checklist_comments',
    ];

    protected $with = [
        'project:id,name,team_id,status,siteimprove_id',
    ];

    protected $casts = [
        'checklist_comments' => 'array',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function guidelines(): HasMany
    {
        return $this->hasMany(ScopeGuideline::class);
    }

    public function rules(): HasMany
    {
        return $this->hasMany(ScopeRule::class);
    }

    public function issues(): HasMany
    {
        return $this->hasMany(Issue::class);
    }

    public function pages(): HasMany
    {
        return $this->hasMany(Page::class)
            ->where('url', $this->url);
    }

    public function latestPage(): HasOne
    {
        return $this->hasOne(Page::class)
            ->where('url', $this->url)
            ->latest('retrieved_at');
    }

    public function currentPage(): BelongsTo
    {
        return $this->belongsTo(Page::class, 'current_page_id');
    }

    public function chats(User $user): MorphMany
    {
        return $this->morphMany(ChatHistory::class, 'context')
            ->where('agent_id', Agent::findAgent(Agents::ModelChatAgent)->id)
            ->where('user_id', $user->id);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable')->oldest();
    }

    public function pageHasBeenRetrieved(): bool
    {
        return $this->latestPage()->exists();
    }

    /**
     * Get the content of the current page, or the latest page if no current page is set.
     */
    public function getPageContent(): ?string
    {
        return $this->currentPage?->getPageContent()
            ?? $this->latestPage?->getPageContent()
            ?? null;
    }

    /**
     * The size of the stored page content in bytes, without reading it.
     */
    public function getPageContentSize(): int
    {
        return $this->currentPage?->getPageContentSize()
            ?? $this->latestPage?->getPageContentSize()
            ?? 0;
    }

    /**
     * Whether the stored page is too large to send to a model.
     *
     * Oversized pages otherwise fail mid-stream with a context-window error
     * from the gateway, after the user has already waited on the request.
     * See docs/oversized-page-content.md.
     */
    public function pageContentExceedsAnalysisLimit(): bool
    {
        return $this->getPageContentSize() > config('cornell_ai.page_content_char_limit');
    }

    /**
     * A message explaining that the page is too large to analyze, or null when
     * it is within the limit.
     *
     * The refusal is logged because the count of real user encounters is what
     * would justify building the content minimizing described in
     * docs/oversized-page-content.md.
     */
    public function pageContentAnalysisRefusal(): ?string
    {
        if (! $this->pageContentExceedsAnalysisLimit()) {
            return null;
        }

        $size = $this->getPageContentSize();

        Log::warning('AI analysis refused: page content exceeds the analysis limit', [
            'scope_id' => $this->id,
            'url' => $this->url,
            'size' => $size,
            'limit' => config('cornell_ai.page_content_char_limit'),
        ]);

        return 'The retrieved content for this page is too large for AI analysis ('
            .number_format($size / 1_048_576, 1).' MB). '
            .'Narrowing the scope to a smaller page will allow analysis.';
    }

    /**
     * Store a new current page
     */
    public function setPageContent(string $url, ?string $content): void
    {
        $page = Page::createPageContent($this, $url, $content);
        // @TODO: Should this be a function on Page?
        if ($siteImproveUrl = app(SiteimproveService::class)->getPageReportUrl($url)) {
            $page->siteimprove_report_url = $siteImproveUrl;
            $page->save();
        }
        $this->setCurrentPage($page);
    }

    /**
     * Set (or unset) a page as the current page.
     */
    public function setCurrentPage(?Page $page = null): void
    {
        if ($page && $page->scope_id !== $this->id) {
            throw new \InvalidArgumentException('The page does not belong to this scope.');
        }

        $this->update(['current_page_id' => $page?->id]);
    }

    public function generateScopeGuidelines(): void
    {
        $guidelinesList = Guideline::all()->pluck('id')
            ->map(function ($id) {
                return [
                    'guideline_id' => $id,
                    'completed' => false,
                    'status' => GuidelineStatus::NotStarted,
                ];
            });
        $this->guidelines()->createMany($guidelinesList);
    }

    public function shortUrl(): ?string
    {
        if (blank($this->url)) {
            return null;
        }

        // Return the path and anything after that
        $parts = parse_url($this->url);
        if ($parts === false) {
            return null;
        }

        $path = $parts['path'] ?? '/';
        $query = isset($parts['query']) ? '?' . $parts['query'] : '';
        $fragment = isset($parts['fragment']) ? '#' . $parts['fragment'] : '';

        return $path . $query . $fragment;
    }
}
