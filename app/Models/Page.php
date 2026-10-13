<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Page extends Model
{
    const string STORAGE_PATH = 'page_content';

    protected $fillable = [
        'scope_id',
        'url',
        'page_content',
        'retrieved_at',
        'siteimprove_page_id',
        'siteimprove_report_url',
    ];

    protected $casts = [
        'retrieved_at' => 'datetime',
    ];

    private ?int $pageContentSize = null;

    protected static function booted(): void
    {
        static::deleting(function (Page $page) {
            if (!empty($page->page_content) && Storage::exists($page->page_content)) {
                Storage::delete($page->page_content);
            }
        });
    }

    /**
     * Get the scope that owns the page.
     */
    public function scope(): BelongsTo
    {
        return $this->belongsTo(Scope::class);
    }

    private static function getStorageFilePath(int $scope_id): string
    {
        $dateString = now()->format('Y-m-d_H-i-s');

        return self::STORAGE_PATH . "/$scope_id/$dateString.html";
    }

    /**
     * @throws Exception
     */
    public static function createPageContent(Scope $scope, string $url, ?string $content = ''): static
    {
        $page = self::make([
            'scope_id' => $scope->id,
            'url' => $url,
        ]);
        $page->storePageContent($content ?? '');

        return $page->refresh();
    }

    public function getPageContent(): string
    {
        return empty($this->page_content) ? '' : (Storage::get($this->page_content) ?: '');
    }

    /**
     * The size of the stored content in bytes, read from the file rather than
     * its contents so a multi-megabyte page can be measured without loading it.
     *
     * Remembered per instance, since a single size-limit check asks for it
     * more than once.
     */
    public function getPageContentSize(): int
    {
        if ($this->pageContentSize !== null) {
            return $this->pageContentSize;
        }

        if (empty($this->page_content) || ! Storage::exists($this->page_content)) {
            return $this->pageContentSize = 0;
        }

        return $this->pageContentSize = Storage::size($this->page_content);
    }

    /**
     * @throws Exception If the content cannot be stored.
     */
    public function storePageContent(string $content): void
    {
        $storageFilePath = self::getStorageFilePath($this->scope_id);

        if (Storage::put($storageFilePath, $content)) {
            $this->page_content = $storageFilePath;
            $this->pageContentSize = null;
            $this->retrieved_at = now();
            $this->save();
        } else {
            throw new Exception("Failed to store page content for scope ID $this->scope_id");
        }
    }
}
