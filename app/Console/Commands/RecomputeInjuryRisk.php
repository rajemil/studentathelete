<?php

namespace App\Console\Commands;

use App\Services\InjuryRisk\InjuryRiskService;
use Illuminate\Console\Command;

class RecomputeInjuryRisk extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'injury-risk:recompute {--no-output : Do not output counts}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recompute fatigue score and injury risk for athletes';

    /**
     * Execute the console command.
     */
    public function handle(InjuryRiskService $risk): int
    {
        $count = $risk->recomputeAll();

        if (! $this->option('no-output')) {
            $this->info("Updated {$count} athlete profiles.");
        }

        return self::SUCCESS;
    }
}
