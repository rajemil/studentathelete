<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Organization;
use App\Models\OrganizationSetting;

class OrganizationSettingSeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::first();
        if (!$organization) return;

        OrganizationSetting::updateOrCreate(
            ['organization_id' => $organization->id],
            [
                'about_us' => 'Student Athlete Information Management System with enterprise-grade analytics and modern UI.',
                'privacy_policy' => 'Privacy Policy Content',
                'contact_email' => 'contact@saims.edu',
                'contact_phone' => '+1 234 567 8900',
                'social_links' => [],
            ]
        );
    }
}
