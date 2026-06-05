<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationService extends Model
{
    protected $guarded = [];
    protected $casts = ['from_date' => 'date', 'to_date' => 'date'];
    
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
