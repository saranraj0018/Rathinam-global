<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationEducation extends Model
{
    protected $table = 'application_educations';
    protected $guarded = [];
    protected $casts = ['passing_date' => 'date'];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }
}
