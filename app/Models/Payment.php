<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'payment_id',
        'order_id',
        'amount',
        'application_id',
        'user_id',
        'status',
        'type',
        'transaction_id',
    ];
}
