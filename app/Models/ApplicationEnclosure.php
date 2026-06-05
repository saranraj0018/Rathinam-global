<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationEnclosure extends Model
{
    protected $guarded = [];
    protected $casts = ['checked' => 'boolean'];
    
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
