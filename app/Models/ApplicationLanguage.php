<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationLanguage extends Model
{
    protected $guarded = [];
    protected $casts = ['can_read' => 'boolean', 'can_write' => 'boolean', 'can_speak' => 'boolean'];
    public function application()
    {
        return $this->belongsTo(Application::class);
    }
}
