<?php

namespace App\Enums;

/**
 * Whether a larger value for a metric is good, bad, or neither.
 *
 * `Neutral` is not a hedge. Some figures describe a run without judging it —
 * how many guidelines were returned, how many steps were taken, how far the
 * answer drifted from the one in history — and reporting those as wins or
 * losses would invent a preference nobody holds.
 */
enum MetricDirection: string
{
    use NamedEnum;

    case HigherBetter = 'higher_better';
    case LowerBetter = 'lower_better';
    case Neutral = 'neutral';

    /**
     * The sign that makes a difference an improvement, or 0 when neither does.
     */
    public function improvementSign(): int
    {
        return match ($this) {
            self::HigherBetter => 1,
            self::LowerBetter => -1,
            self::Neutral => 0,
        };
    }
}
