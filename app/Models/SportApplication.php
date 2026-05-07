<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SportApplication extends Model
{
    protected $fillable = [
        'sport_id',
        'user_id',
        'status',
        'qualification_passed',
        'qualification_detail',
        'student_message',
    ];

    protected function casts(): array
    {
        return [
            'qualification_passed' => 'boolean',
            'qualification_detail' => 'array',
        ];
    }

    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
