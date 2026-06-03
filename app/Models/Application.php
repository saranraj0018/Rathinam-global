<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Application extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'completed_steps' => 'array',
        'dob'             => 'date',
        'submitted_at'          => 'datetime',
        'single_girl_child'     => 'boolean',
        'differently_abled'     => 'boolean',
        'address_same'          => 'boolean',
        'eligibility_qualified' => 'boolean',
        'enclosures_confirm'    => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function languages(): HasMany
    {
        return $this->hasMany(ApplicationLanguage::class);
    }
    public function educations(): HasMany
    {
        return $this->hasMany(ApplicationEducation::class);
    }
    public function services(): HasMany
    {
        return $this->hasMany(ApplicationService::class);
    }
    public function projects(): HasMany
    {
        return $this->hasMany(ApplicationProject::class);
    }
    public function courses(): HasMany
    {
        return $this->hasMany(ApplicationCourse::class);
    }
    public function aspirations(): HasMany
    {
        return $this->hasMany(ApplicationCareerAspiration::class);
    }
    public function enclosures(): HasMany
    {
        return $this->hasMany(ApplicationEnclosure::class);
    }
    public function documents(): HasMany
    {
        return $this->hasMany(ApplicationDocument::class);
    }

}
