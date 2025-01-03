<?php

namespace App\Models;

use App\Enums\GuidelineTools;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Guideline extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'name',
        'criterion_id',
        'category_id',
        'notes',
        'tools',
    ];

    protected $casts = [
        'tools' => AsEnumCollection::class.':'.GuidelineTools::class,
    ];

    public function criterion(): BelongsTo
    {
        return $this->belongsTo(Criterion::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function actRules(): BelongsToMany
    {
        return $this->belongsToMany(ActRule::class);
    }

    public function siteimproveRules(): BelongsToMany
    {
        return $this->belongsToMany(SiteimproveRule::class, 'siteimprove_rule_guideline');
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    public function hasAutomatedAssessment(): bool
    {
        return $this->tools->contains(GuidelineTools::Siteimprove);
    }
}
