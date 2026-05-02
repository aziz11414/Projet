<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Job extends Model
{
    protected $table = 'job_offers';

    protected $fillable = [
        'title',
        'description',
        'required_skills',
        'status',
    ];

    protected $casts = [
        'required_skills' => 'array',
    ];

    public function matchScores(): HasMany
    {
        return $this->hasMany(MatchScore::class);
    }
}