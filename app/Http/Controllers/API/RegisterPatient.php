<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\User;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class RegisterPatient extends Controller
{
    public function registerPatient(Request $request) {
        try {
            // Start a database transaction
            DB::beginTransaction();

            // Validate the incoming request data
            $request->validate([
                'email' => 'required|email|unique:users',
                'password' => 'required|min:8',
                'first_name' => 'required',
                'last_name' => 'required',
                'birthday' => 'required|date',
                'sex' => 'required|in:Male,Female', // You can specify the valid options here
                'mobile_number' => 'required',
            ]);

            // Create a new user
            $user = new User();
            $user->email = $request->input('email');
            $user->password = bcrypt($request->input('password'));
            $user->user_type = 'patient'; // Set the user type to 'patient'
            $user->save();

            // Create a new patient associated with the user
            $patient = new Patient();
            $patient->user_id = $user->id;
            $patient->first_name = $request->input('first_name');
            $patient->last_name = $request->input('last_name');
            $patient->middle_name = $request->input('middle_name');
            $patient->extension_name = $request->input('extension_name');
            $patient->birthday = $request->input('birthday');
            $patient->sex = $request->input('sex');
            $patient->mobile_number = $request->input('mobile_number');
            // You can set the other optional fields here

            $patient->save();

            // Commit the database transaction
            DB::commit();

            return response()->json(['message' => 'Patient registered successfully'], 201);
        } catch (Exception $e) {
            // Rollback the database transaction in case of an error
            DB::rollBack();
            return response()->json(['message' => 'Registration failed. Please try again.'], 500);
        }
    }
}
