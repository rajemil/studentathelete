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
        $faker = fake();
        $now = Carbon::now();

        $seedDemo = filter_var(env('SEED_DEMO_DATA', false), FILTER_VALIDATE_BOOL);

        // Always seed the minimal "core" setup so the app can be used manually.
        $defaultOrg = Organization::query()->firstOrCreate(
            ['slug' => 'default'],
            ['name' => 'Student Athlete Ai']
        );

        $admin = User::query()->firstOrCreate(
            ['email' => 'ai.studentathlete001@gmail.com'],
            [
                'organization_id' => $defaultOrg->id,
                'role' => 'admin',
                'name' => 'Raje Mil',
                'password' => Hash::make('password'),
                'email_verified_at' => $now,
            ]
        );

        if (DB::getSchemaBuilder()->hasTable('activity_log')) {
            activity()->causedBy($admin)->withProperties(['organization_id' => $defaultOrg->id])->log('seed_core_completed');
        }

        $this->call([
            OrganizationSettingSeeder::class,
            TeamMemberSeeder::class,
        ]);
    }
}
