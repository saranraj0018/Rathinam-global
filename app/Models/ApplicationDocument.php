<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationDocument extends Model
{
    protected $guarded = [];
    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    // app/Models/ApplicationDocument.php
    public function getFileSizeHumanAttribute(): string
    {
        $b = $this->file_size;
        if (! $b) return '—';
        $u = ['B', 'KB', 'MB', 'GB', 'TB'];
        $p = min((int) floor(log($b, 1024)), count($u) - 1);

        return round($b / (1024 ** $p), 1) . ' ' . $u[$p];
    }
}
