<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Doctor;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

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
            'email' => 'required|email|unique:dentist,email',
        ]);
        Log::info('Input data: ' . json_encode($request->all()));
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

    public function editDoctor(Request $request, $id) {
        $validator = Validator::make($request->all(), [
            'dentist' => 'sometimes|string',
            'specialization' => 'sometimes|string',
            'mobile_number' => 'sometimes|string',
            'email' => 'sometimes|email|unique:dentist,email,' . $id,
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }
    
        try {
            $doctor = Doctor::find($id);
            
            if (!$doctor) {
                return response()->json(['error' => 'Doctor not found'], 404);
            }
    
            $doctor->update($request->all());
            return response()->json(['message' => 'Doctor updated successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'An error occurred while updating the doctor'], 500);
        }
    }

    public function deleteDoctor($id) {
        try {
            $doctor = Doctor::find($id);
    
            if (!$doctor) {
                return response()->json(['error' => 'Doctor not found'], 404);
            }
    
            $doctor->delete();
            return response()->json(['message' => 'Doctor deleted successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'An error occurred while deleting the doctor'], 500);
        }
    }
    
    
}
