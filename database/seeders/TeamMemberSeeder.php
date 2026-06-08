<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Organization;
use App\Models\TeamMember;

class TeamMemberSeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::first();
        if (!$organization)
            return;

        $members = [
            [
                'name'        => 'Raje Mil F. Borda',
                'role'        => 'Programmer, Documentator',
                'description' => 'Driven by a philosophy that true mastery lies in patience, building deep roots in logic and character to sustain a lifetime of meaningful impact.',
            ],
            [
                'name'        => 'Kurt Brian Tacdoro',
                'role'        => 'Documentator, Presenter',
                'description' => "Believes that life's greatest values are found in clarity and expression—striving to live intentionally while articulating a vision for a purposeful future.",
            ],
            [
                'name'        => 'Ray Valentine Y. Agoncillo',
                'role'        => 'Programmer, Documentator',
                'description' => 'Focused on balancing curiosity with execution, viewing life as an ongoing experiment in creating harmony between personal passion and practical contribution.',
            ],
        ];

        // Ensure only these members exist
        $names = collect($members)->pluck('name')->toArray();
        TeamMember::where('organization_id', $organization->id)
            ->whereNotIn('name', $names)
            ->delete();

        foreach ($members as $member) {
            TeamMember::updateOrCreate(
                [
                    'organization_id' => $organization->id,
                    'name' => $member['name'],
                ],
                [
                    'role' => $member['role'],
                    'description' => $member['description'],
                ]
            );
        }
    }
}
