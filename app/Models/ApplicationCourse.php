<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationCourse extends Model
{
    protected $guarded = [];
    protected $casts = ['completed' => 'boolean'];
    
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
