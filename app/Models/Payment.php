<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'application_id',
        'user_id',
        'amount',
        'status',
        'type',
        'transaction_id',
    ];
}
