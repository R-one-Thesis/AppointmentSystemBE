<?php

namespace App\Models;

use App\Models\Patient;
use App\Models\Schedule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'schedule_id',
        'services',
        'price',
        'duration',
        'approved',
    ];

    public function schedule() {
        return $this->belongsTo(Schedule::class, 'doctors_id', 'id');
    }

    public function patient() {

        return $this->belongsTo(Patient::class, 'patient_id', 'id');

    }

}
