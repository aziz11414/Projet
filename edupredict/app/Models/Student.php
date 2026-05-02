<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Student extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'age',
        'study_time',
        'failures',
        'absence',
        'filiere',
        'probability',
        'result',
    ];

    protected function casts(): array
    {
        return [
            'probability' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
