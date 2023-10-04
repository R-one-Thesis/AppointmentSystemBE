<?php

namespace App\Models;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class History extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'physician_data',
        'hospitalizations_data',
        'surgery_data',
        'illness_disease',
        'medication',
        'allergies',
        'pregnancy',
        'menstrual_data'
    ];


    



    public function history() {
        return $this->belongsTo(Patient::class, 'patient_id');
    }
}
