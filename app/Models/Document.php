<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'slug',
        'version',
        'title',
        'content',
        'is_current',
    ];

    protected $casts = [
        'is_current' => 'boolean',
    ];

    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function versions(Builder $query, string $slug): void
    {
        $query->where('slug', $slug)
            ->orderBy('version', 'desc');
    }

    public function setCurrentVersion(int $id): Document
    {
        Document::where('slug', $this->slug)->update(['is_current' => false]);
        Document::where('id', $id)->update(['is_current' => true]);

        return Document::find($id);
    }

    public function createNewVersion(array $attributes): self
    {
        // mark this one as historical
        $this->update(['is_current' => false]);

        $nextVersionNumber = (Document::where('slug', $this->slug)->max('version') ?? 0) + 1;

        // spin up the new row, bumping version
        return self::create([
            'slug'      => $this->slug,
            'title'     => $attributes['title'],
            'content'   => $attributes['content'],
            'is_current' => true,
            'version'   => $nextVersionNumber,
        ]);
    }

    public static function get(string $slug): static
    {
        return Document::where('slug', $slug)
            ->where('is_current', true)
            ->firstOr(
                fn () => new static([
                    'slug' => $slug,
                    'title' => 'New Document',
                    'content' => '',
                ])
            );
    }
}
