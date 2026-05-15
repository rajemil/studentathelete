<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Course extends Model
{
    protected $fillable = ['organization_id', 'name', 'code'];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
