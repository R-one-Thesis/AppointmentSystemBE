<?php

namespace App\Models;

use App\Models\Doctor;
use App\Models\Booking;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Schedule extends Model
{
    use HasFactory;
    protected $fillable = [
        'doctors_id',
        'services',
        'date',
        'time_start',
        'duration',
        'booked'
    ];

    protected $casts = [
        'services' => 'array',
    ];

    public function doctor() {
        return $this->belongsTo(Doctor::class, 'doctors_id', 'id');
    }

    public function booking() {
        return $this->hasOne(Booking::class, 'schedule_id', 'id');
    }
}
