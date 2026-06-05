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
                'name' => 'Raje Mil F. Borda',
                'role' => 'Programmer, Documentator',
                'description' => 'We cut planning time in half and improved lineup decisions immediately.',
            ],
            [
                'name' => 'Kurt Brian Tacdoro',
                'role' => 'Documentator, Presenter',
                'description' => 'The rankings and win probability changed how we prepare for meets.',
            ],
            [
                'name' => 'Ray Valentine Y. Agoncillo',
                'role' => 'Programmer, Documentator',
                'description' => 'Clean UI, fast workflows, and the analytics are easy to trust.',
            ],
        ];

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
