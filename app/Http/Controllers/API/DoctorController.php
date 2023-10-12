<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Doctor;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class DoctorController extends Controller
{
    public function viewDoctors() {

        $dentist = Doctor::all();

        if ($dentist->isEmpty()) {
            return response()->json(['message' => 'No data found']);
        }    

        return response()->json(['message' => 'data found', 'dentist' => $dentist]);
    }

    public function addDoctor(Request $request){
        $validator = Validator::make($request->all(), [
            'dentist' => 'required|string',
            'specialization' => 'required|string',
            'mobile_number' => 'required|string',
            'email' => 'required|email|unique:dentists,email',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        try {
            Doctor::create($request->all());
            return response()->json(['message' => 'Dentist added successfully'], 201);
        } catch (Exception $e) {
            return response()->json(['error' => 'An error occurred while adding the dentist'], 500);
        }
    }
}
