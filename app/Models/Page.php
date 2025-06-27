<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Page extends Model
{
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

    /**
     * Get the scope that owns the page.
     */
    public function scope(): BelongsTo
    {
        return $this->belongsTo(Scope::class);
    }
}
