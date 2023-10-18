<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\User;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

class RegisterPatient extends Controller
{
    public function registerPatient(Request $request) {
        // return $request;
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
                'home_address' => 'required'
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
            $patient->home_address = $request->input('home_address');
            $patient->sex = $request->input('sex');
            $patient->mobile_number = $request->input('mobile_number');
            // You can set the other optional fields here

            $patient->save();

            // Commit the database transaction
            DB::commit();

            return response()->json(['message' => 'Patient registered successfully'], 201);
        } catch (ValidationException $e) {
            // Validation errors
            DB::rollBack();
    
            $errors = $e->validator->getMessageBag();
            
            if ($errors->has('email')) {
                // Handle the specific email uniqueness error
                return response()->json(['message' => 'Email already in use'], 422);
            }
    
            // Handle other validation errors here
            return response()->json(['message' => 'Validation failed', 'errors' => $errors], 422);
        } 
        catch (QueryException $e) {
            // Database query errors
            DB::rollBack();
            return response()->json(['message' => 'Database error occurred', 'error' => $e->getMessage()], 500);
        } catch (Exception $e) {
            // Other unexpected errors
            DB::rollBack();
            return response()->json(['message' => 'An unexpected error occurred'], 500);
        }
    }
}
