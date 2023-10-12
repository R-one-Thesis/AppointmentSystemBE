<?php

namespace App\Models;

use App\Models\Doctor;
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

    public function doctor() {
        return $this->belongsTo(Doctor::class, 'doctors_id', 'id');
    }
}
