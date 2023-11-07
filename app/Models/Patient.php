<?php

namespace App\Models;

use App\Models\User;
use App\Models\History;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'middle_name',
        'extension_name',
        'birthday',
        'sex',
        'religion',
        'home_address',
        'home_phone_number',
        'office_address',
        'work_phone_number',
        'mobile_number',
        'marital_status',
        'spouse',
        'person_responsible_for_the_account',
        'person_responsible_mobile_number',
        'relationship',
        'referal_person',
        'user_id'
    ];



    public function user() {

        return $this->belongsTo(User::class, 'user_id', 'id');

    }

    public function history() {
        return $this->hasOne(History::class);
    }
    
}
