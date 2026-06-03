<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationService extends Model
{
    protected $guarded = [];
    protected $casts = ['from_date' => 'date', 'to_date' => 'date'];
    public function application()
    {
        return $this->belongsTo(Application::class);
    }
}
