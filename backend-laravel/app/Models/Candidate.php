<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Candidate extends Model
{
    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'cv_path',
        'extracted_text',
        'summary',
    ];

    protected $appends = [
        'cv_url',
    ];

    public function matchScores(): HasMany
    {
        return $this->hasMany(MatchScore::class);
    }

    public function getCvUrlAttribute(): ?string
    {
        if (!$this->cv_path) {
            return null;
        }

        return asset('storage/' . $this->cv_path);
    }
}