<?php

namespace App\Console\Commands;

use App\Models\SiaRule;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ParseAlfaRulesAct extends Command
{
    protected $signature = 'app:parse-alfa-rules-act';
    protected $description = 'Imports rules from alfa-assisted.csv and alfa-automated.csv into the sia_rules table';

    public function handle()
    {
        $rules = collect();
        foreach (['alfa-assisted.csv', 'alfa-automated.csv'] as $file) {
            $stream = Storage::readStream($file);
            // Skip the header line
            fgetcsv($stream);
            while ($data = fgetcsv($stream, 0, "\t")) { // Specify tab as the delimiter
                $id = trim($data[2]);
                $rules[] = [
                    'id' => str_replace('sia-r', '', $id),
                    'act_rule_id' => trim($data[0]),
                ];
            }
            fclose($stream);
        }

        // Process and insert rules
        $uniqueRules = $rules->unique(fn($rule) => $rule['id'] . $rule['act_rule_id']);

        foreach ($uniqueRules as $rule) {
            SiaRule::updateOrCreate(['id' => $rule['id']], $rule);
        }

        $this->info("Imported " . $uniqueRules->count() . " unique rules into the database.");
    }
}
