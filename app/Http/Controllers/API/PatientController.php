<?php

namespace App\Http\Controllers\API;

use App\Models\Patient;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PatientController extends Controller
{
    public function viewAllPatients(){

        $patients = Patient::all();

        if ($patients->isEmpty()) {
            return response()->json(['message' => 'No data found']);
        }

        return response()->json(['patiens' => $patients]);
    }
}
