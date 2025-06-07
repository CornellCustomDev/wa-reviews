<?php

namespace App\Models;

use App\Enums\GuidelineStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Scope extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'title',
        'url',
        'notes',
        'comments'
    ];

    protected $with = [
        'project:id,name,team_id,status,siteimprove_id',
    ];

    protected $casts = [
        'comments' => 'array',
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
        return $this->hasMany(Page::class);
    }

    public function latestPage(): HasOne
    {
        return $this->hasOne(Page::class)->latest('retrieved_at');
    }

    public function currentPage(): BelongsTo
    {
        return $this->belongsTo(Page::class, 'current_page_id');
    }

    public function chats(User $user): MorphMany
    {
        return $this->morphMany(ChatHistory::class, 'context')
            ->where('user_id', $user->id);
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
        return $this->currentPage?->page_content
            ?? $this->latestPage?->page_content
            ?? null;
    }

    /**
     * Store a new current page
     */
    public function setPageContent(string $content): void
    {
        $page = $this->pages()->create([
            'url' => $this->url,
            'page_content' => $content,
            'retrieved_at' => now(),
        ]);
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

}
