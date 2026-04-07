<?php

namespace App\Console\Commands;

use App\Services\Insights\InsightsService;
use Illuminate\Console\Command;

class GenerateInsights extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'insights:generate {--no-output : Do not output counts}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate and persist smart insights';

    /**
     * Execute the console command.
     */
    public function handle(InsightsService $insights): int
    {
        $count = $insights->generate();

        if (! $this->option('no-output')) {
            $this->info("Generated {$count} insights.");
        }

        return self::SUCCESS;
    }
}
