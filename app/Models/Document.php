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
        'is_current',
    ];

    protected $casts = [
        'is_current' => 'boolean',
    ];

    public function versions()
    {
        return static::where('slug', $this->slug)
            ->orderBy('version', 'desc');
    }

    public function scopeCurrentVersion($query)
    {
        return $query->where('is_current', true);
    }

    public function setCurrentVersion(int $id)
    {
        $this->versions()->update(['is_current' => false]);

        return $this->versions()->where('id', $id)->update(['is_current' => true]);
    }

    public function createNewVersion(array $attributes): self
    {
        // mark this one as historical
        $this->update(['is_current' => false]);

        $nextVersion = ($this->versions()->max('version') ?? 0) + 1;

        // spin up the new row, bumping version
        return self::create([
            'slug'      => $this->slug,
            'title'     => $attributes['title'],
            'content'   => $attributes['content'],
            'is_current' => true,
            'version'   => $nextVersion,
        ]);
    }

    public static function get(string $slug): static
    {
        return static::where('slug', $slug)->currentVersion()->firstOr(
            fn () => new static(['slug' => $slug, 'title' => 'New Document', 'content' => ''])
        );
    }
}
