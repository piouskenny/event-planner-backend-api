<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Events extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'description',
        'tags',
        'start_date',
        'end_date',
        'location_link',
        'attendance_capacity',
        'ticket_pricing',
        'ticket_price',
        'user_id'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
