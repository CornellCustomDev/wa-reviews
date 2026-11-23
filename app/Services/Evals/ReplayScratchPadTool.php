<?php

namespace App\Services\Evals;

use App\Ai\Prism\Tools\ScratchPadTool;

/**
 * The recommender's scratch pad as a replay sees it: the same name,
 * parameters and responses, without the write.
 *
 * Production appends every note to `scratch_pad.txt` on the default disk,
 * which a database rollback cannot undo. Faking the disk is not an option
 * either, since the stored pages a replay reads live on the same disk.
 */
class ReplayScratchPadTool extends ScratchPadTool
{
    public function __invoke(string $action, string $data): string
    {
        return match ($action) {
            'store', 'append' => json_encode(['status' => 'success', 'message' => 'Data appended to scratch pad.']),
            default => json_encode(['error' => 'invalid_action']),
        };
    }
}
