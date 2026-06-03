<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Application extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'dob'                => 'date',
        'submitted_at'       => 'datetime',
        'single_girl_child'  => 'boolean',
        'differently_abled'  => 'boolean',
        'address_same'       => 'boolean',
        'eligibility_qualified' => 'boolean',
        'enclosures_confirm' => 'boolean',
    ];

    public function languages()
    {
        return $this->hasMany(ApplicationLanguage::class);
    }
    public function educations()
    {
        return $this->hasMany(ApplicationEducation::class);
    }
    public function services()
    {
        return $this->hasMany(ApplicationService::class);
    }
    public function projects()
    {
        return $this->hasMany(ApplicationProject::class);
    }
    public function courses()
    {
        return $this->hasMany(ApplicationCourse::class);
    }
    public function aspirations()
    {
        return $this->hasMany(ApplicationCareerAspiration::class);
    }
    public function enclosures()
    {
        return $this->hasMany(ApplicationEnclosure::class);
    }
    public function documents()
    {
        return $this->hasMany(ApplicationDocument::class);
    }
 
}
