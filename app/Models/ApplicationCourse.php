<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationCourse extends Model
{
    protected $guarded = [];
    protected $casts = ['completed' => 'boolean'];
    public function application()
    {
        return $this->belongsTo(Application::class);
    }
}
