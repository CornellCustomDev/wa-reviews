<?php

namespace App\Models;

use App\Services\AccessibilityAnalyzer\RuleRunner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScopeRule extends Model
{
    protected $fillable = [
        'scope_id',
        'guideline_id',
        'rule_class',
        'rule_name',
        'css_selector',
        'description',
        'ai_assessment',
        'ai_description',
        'ai_reasoning',
    ];

    public function scope(): BelongsTo
    {
        return $this->belongsTo(Scope::class);
    }

    public function getRuleRunner(): RuleRunner
    {
        return new $this->rule_class;
    }

    public function hasAiAssessment(): bool
    {
        return $this->ai_assessment !== null;
    }
}
