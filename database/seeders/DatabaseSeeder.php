<?php

namespace Database\Seeders;

use App\Models\CoachAssignment;
use App\Models\Event;
use App\Models\InjuryRecord;
use App\Models\Organization;
use App\Models\ParticipationLog;
use App\Models\PerformanceScore;
use App\Models\PlayerStat;
use App\Models\Profile;
use App\Models\Sport;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Keep seeds deterministic-ish without being brittle.
        $faker = fake();
        $now = Carbon::now();

        // Ensure the default org exists (migration normally creates it).
        $defaultOrg = Organization::query()->firstOrCreate(
            ['slug' => 'default'],
            ['name' => 'Default Organization']
        );

        // Create a second organization for multi-tenant sanity checks.
        $secondOrg = Organization::query()->firstOrCreate(
            ['slug' => 'rival-academy'],
            ['name' => 'Rival Academy']
        );

        foreach ([$defaultOrg, $secondOrg] as $org) {
            $orgId = $org->id;

            // Users
            $admin = User::factory()->create([
                'organization_id' => $orgId,
                'role' => 'admin',
                'name' => $org->slug === 'default' ? 'Admin' : 'Admin ('.$org->name.')',
                'email' => $org->slug === 'default' ? 'admin@default.test' : 'admin@'.$org->slug.'.test',
                'password' => Hash::make('password'),
                'email_verified_at' => $now,
            ]);

            $coaches = User::factory()->count(3)->create([
                'organization_id' => $orgId,
                'role' => 'coach',
                'email_verified_at' => $now,
            ]);

            $instructors = User::factory()->count(2)->create([
                'organization_id' => $orgId,
                'role' => 'instructor',
                'email_verified_at' => $now,
            ]);

            $students = User::factory()->count(18)->create([
                'organization_id' => $orgId,
                'role' => 'student',
                'email_verified_at' => $now,
            ]);

            // Profiles (kept lightweight; enough to drive UI + injury-risk computations)
            foreach ($students as $s) {
                Profile::query()->create([
                    'user_id' => $s->id,
                    'age' => $faker->numberBetween(15, 22),
                    'gender' => $faker->randomElement(['male', 'female', 'other']),
                    'address' => $faker->city().', '.$faker->stateAbbr(),
                    'height_cm' => $faker->randomFloat(2, 150, 205),
                    'weight_kg' => $faker->randomFloat(2, 45, 110),
                    'bmi' => null,
                    'fatigue_score' => $faker->numberBetween(10, 80),
                    'injury_risk' => $faker->randomElement(['low', 'medium', 'high']),
                    'sports_interested' => [],
                ]);
            }

            foreach ($coaches as $c) {
                Profile::query()->create([
                    'user_id' => $c->id,
                    'age' => $faker->numberBetween(23, 60),
                    'gender' => $faker->randomElement(['male', 'female', 'other']),
                    'address' => $faker->city().', '.$faker->stateAbbr(),
                    'field_expertise' => $faker->randomElement(['Strength & Conditioning', 'Tactics', 'Skills', 'Performance']),
                    'achievements' => $faker->sentence(),
                    'profession' => $faker->jobTitle(),
                    'coaching_experience_years' => $faker->numberBetween(1, 25),
                ]);
            }

            // Sports
            $sportDefs = [
                ['name' => 'Basketball', 'slug' => 'basketball'],
                ['name' => 'Soccer', 'slug' => 'soccer'],
                ['name' => 'Volleyball', 'slug' => 'volleyball'],
                ['name' => 'Tennis', 'slug' => 'tennis'],
            ];

            $sports = collect($sportDefs)->map(function (array $def) use ($orgId) {
                return Sport::query()->create([
                    'organization_id' => $orgId,
                    'name' => $def['name'],
                    'slug' => $def['slug'].'-'.$orgId, // unique per org
                    'description' => null,
                ]);
            });

            // Teams + rosters + assignments
            foreach ($sports as $sport) {
                $primaryCoach = $coaches->random();

                $teamA = Team::query()->create([
                    'organization_id' => $orgId,
                    'name' => $sport->name.' A',
                    'sport_id' => $sport->id,
                    'primary_coach_id' => $primaryCoach->id,
                ]);

                $teamB = Team::query()->create([
                    'organization_id' => $orgId,
                    'name' => $sport->name.' B',
                    'sport_id' => $sport->id,
                    'primary_coach_id' => $coaches->random()->id,
                ]);

                CoachAssignment::query()->firstOrCreate([
                    'coach_id' => $primaryCoach->id,
                    'team_id' => $teamA->id,
                    'assignment_role' => 'head_coach',
                ], [
                    'starts_on' => $now->copy()->subMonths(2)->toDateString(),
                    'ends_on' => null,
                ]);

                // Students join sport and get split across teams
                $sportStudents = $students->shuffle()->take(10)->values();
                $sport->students()->syncWithoutDetaching($sportStudents->pluck('id')->all());

                $teamAStudents = $sportStudents->take(5)->values();
                $teamBStudents = $sportStudents->slice(5)->values();

                $teamA->students()->syncWithoutDetaching(
                    $teamAStudents->mapWithKeys(fn (User $u, int $idx) => [
                        $u->id => ['rank' => $idx + 1, 'joined_on' => $now->copy()->subDays(30)->toDateString()],
                    ])->all()
                );

                $teamB->students()->syncWithoutDetaching(
                    $teamBStudents->mapWithKeys(fn (User $u, int $idx) => [
                        $u->id => ['rank' => $idx + 1, 'joined_on' => $now->copy()->subDays(30)->toDateString()],
                    ])->all()
                );

                // Scores (recent 30 days)
                foreach ($sportStudents as $student) {
                    foreach (range(0, 6) as $i) {
                        $date = $now->copy()->subDays($i * 4)->toDateString();
                        PerformanceScore::query()->create([
                            'user_id' => $student->id,
                            'sport_id' => $sport->id,
                            'team_id' => null,
                            'category' => 'overall',
                            'score' => $faker->randomFloat(2, 40, 99),
                            'scored_on' => $date,
                            'breakdown' => [
                                'entered_by' => $primaryCoach->id,
                            ],
                        ]);
                    }

                    // Participation logs (training/recovery)
                    foreach (range(0, 10) as $i) {
                        ParticipationLog::query()->create([
                            'organization_id' => $orgId,
                            'user_id' => $student->id,
                            'sport_id' => $sport->id,
                            'activity_type' => $faker->randomElement(['training', 'competition', 'recovery']),
                            'duration_minutes' => $faker->numberBetween(20, 120),
                            'notes' => $faker->boolean(20) ? $faker->sentence() : null,
                            'logged_on' => $now->copy()->subDays($faker->numberBetween(0, 28))->toDateString(),
                        ]);
                    }

                    // Player stats (simple metrics blob)
                    PlayerStat::query()->create([
                        'user_id' => $student->id,
                        'sport_id' => $sport->id,
                        'team_id' => null,
                        'recorded_on' => $now->copy()->subDays($faker->numberBetween(0, 21))->toDateString(),
                        'season' => $now->format('Y').'-'.((int) $now->format('Y') + 1),
                        'metrics' => [
                            'speed' => $faker->numberBetween(40, 95),
                            'stamina' => $faker->numberBetween(40, 95),
                            'agility' => $faker->numberBetween(40, 95),
                        ],
                    ]);
                }

                // Injury records (a few per sport)
                foreach ($sportStudents->shuffle()->take(2) as $student) {
                    InjuryRecord::query()->create([
                        'organization_id' => $orgId,
                        'athlete_user_id' => $student->id,
                        'reported_by_user_id' => $primaryCoach->id,
                        'sport_id' => $sport->id,
                        'title' => $faker->randomElement(['Knee pain', 'Ankle sprain', 'Shoulder strain', 'Back soreness']),
                        'description' => $faker->boolean(60) ? $faker->sentence(12) : null,
                        'status' => $faker->randomElement(['open', 'monitoring', 'cleared']),
                        'occurred_on' => $now->copy()->subDays($faker->numberBetween(1, 45))->toDateString(),
                    ]);
                }

                // Events + participants
                $event = Event::query()->create([
                    'title' => $sport->name.' Training',
                    'description' => 'Seeded training session',
                    'sport_id' => $sport->id,
                    'team_id' => $teamA->id,
                    'created_by' => $primaryCoach->id,
                    'starts_at' => $now->copy()->addDays(2)->setTime(16, 0),
                    'ends_at' => $now->copy()->addDays(2)->setTime(18, 0),
                    'location' => $faker->streetName(),
                    'event_type' => 'training',
                ]);

                $event->participants()->syncWithoutDetaching(
                    $teamAStudents->pluck('id')->mapWithKeys(fn (int $id) => [$id => ['participant_role' => 'student']])->all()
                );
                $event->participants()->syncWithoutDetaching([
                    $primaryCoach->id => ['participant_role' => 'coach'],
                ]);
            }

            // Some activity log entries (if activitylog is installed/migrated)
            if (DB::getSchemaBuilder()->hasTable('activity_log')) {
                activity()->causedBy($admin)->withProperties(['organization_id' => $orgId])->log('seed_completed');
            }
        }
    }
}
