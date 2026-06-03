<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationEducation extends Model
{
    protected $table = 'application_educations';
    protected $guarded = [];
    protected $casts = ['passing_date' => 'date'];
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
