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
        'physician',
        'physaddress',
        'reason',
        'hospitalization_reason',
        'conditions',
        'medication',
        'allergies',
        'pregnant',
        'expected_date',
        'mens_problems',
    ];


    protected $casts = [
        'conditions' => 'array',
        'medication' => 'array',
        'allergies' => 'array',
    ];



    public function history() {
        return $this->belongsTo(Patient::class, 'patient_id');
    }
}
