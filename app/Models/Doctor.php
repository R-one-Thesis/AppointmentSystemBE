<?php

namespace App\Models;

use App\Models\Schedule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Doctor extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'dentist',
        'mobile_number',
        'email'
    ];

    public function schedule() {
        return $this->hasMany(Schedule::class, 'doctors_id', 'id');
    }
}
