<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToOrganization;

class OrganizationSetting extends Model
{
    use BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'about_us',
        'privacy_policy',
        'contact_email',
        'contact_phone',
        'social_links',
    ];

    protected $casts = [
        'social_links' => 'array',
    ];
}
