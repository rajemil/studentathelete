<?php

namespace App\Console\Commands;

use App\Models\Sport;
use App\Models\User;
use App\Services\Training\TrainingRecommendationService;
use Illuminate\Console\Command;

class GenerateWeeklyTrainingPlans extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'training:generate-weekly {--user_id=} {--sport_id=} {--no-output}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate weekly AI training plans for students';

    /**
     * Execute the console command.
     */
    public function handle(TrainingRecommendationService $service): int
    {
        $userId = $this->option('user_id') ? (int) $this->option('user_id') : null;
        $sportId = $this->option('sport_id') ? (int) $this->option('sport_id') : null;

        $sport = $sportId ? Sport::query()->find($sportId) : null;

        $query = User::query()->where('role', 'student')->with('profile');
        if ($userId) {
            $query->where('id', $userId);
        }

        $count = 0;
        foreach ($query->get() as $student) {
            if (! $student->profile) continue;
            $service->generateWeeklyPlan($student, $sport);
            $count++;
        }

        if (! $this->option('no-output')) {
            $this->info("Generated weekly plans for {$count} athlete(s).");
        }

        return self::SUCCESS;
    }
}
