<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'middle_name',
        'extension_name',
        'birthday',
        'home_address',
        'mobile_number',
        'user_id'
    ];


    public function user() {

        return $this->belongsTo(User::class, 'user_id');

    }
}
