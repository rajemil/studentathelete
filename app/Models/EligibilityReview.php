<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EligibilityReview extends Model
{
    use BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'user_id',
        'reviewer_id',
        'review_date',
        'status',
        'comments',
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
            'review_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
