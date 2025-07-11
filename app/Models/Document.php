<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'slug',
        'version',
        'title',
        'content',
        'is_latest',
    ];

    protected $casts = [
        'is_latest' => 'boolean',
    ];

    public function scopeLatestVersion($query)
    {
        return $query->where('is_latest', true);
    }

    public function createNewVersion(array $attributes): self
    {
        // mark this one as historical
        $this->update(['is_latest' => false]);

        // spin up the new row, bumping version
        return self::create([
            'slug'      => $this->slug,
            'title'     => $attributes['title'],
            'content'   => $attributes['content'],
            'is_latest' => true,
            'version'   => $this->version + 1,
        ]);
    }

    public static function get(string $slug): static
    {
        return static::where('slug', $slug)->latestVersion()->firstOr(
            fn () => new static(['slug' => $slug, 'title' => 'New Document', 'content' => ''])
        );
    }
}
