<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceRecord extends Model
{
    use BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'user_id',
        'course_name',
        'date',
        'status',
        'notes',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (!$model->organization_id) {
                $orgId = null;
                if ($model->user_id) {
                    $orgId = User::where('id', $model->user_id)->value('organization_id');
                }
                $model->organization_id = $orgId ?: Organization::defaultId();
            }
        });
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
