<?php

namespace App\Models;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'schedule_id',
    ];

    public function schedule() {
        return $this->belongsTo(Booking::class, 'doctors_id', 'id');
    }

}
