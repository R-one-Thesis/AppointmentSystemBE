<?php

namespace App\Models;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PatientImageRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'image_type',
        'image_path',
    ];

    public function patient() {

        return $this->belongsTo(Patient::class, 'patient_id', 'id');

    }
}
