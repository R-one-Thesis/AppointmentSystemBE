<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\User;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

class RegisterPatient extends Controller
{
    public function registerPatient(Request $request) {
        $validatedData = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'middle_name' => 'sometimes|string',
            'extension_name' => 'sometimes|string',
            'birthday' => 'sometimes|date',
            'sex' => 'required|in:Male,Female',
            'home_address' => 'required|string',
            'mobile_number' => 'sometimes|string',
        ]);

        try {

            DB::beginTransaction();

            $user = User::create([
                'email' => $validatedData['email'],
                'user_type' => "Patient",
                'password' => Hash::make($validatedData['password']), 
            ]);

            $patientData = [
                'user_id' => $user->id,
                'first_name' => $validatedData['first_name'],
                'last_name' => $validatedData['last_name'],
                'sex' => $validatedData['sex'],
                'home_address' => $validatedData['home_address'],
            ];

            if (isset($validatedData['middle_name'])) {
                $patientData['middle_name'] = $validatedData['middle_name'];
            }
    
            if (isset($validatedData['extension_name'])) {
                $patientData['extension_name'] = $validatedData['extension_name'];
            }
    
            if (isset($validatedData['birthday'])) {
                $patientData['birthday'] = $validatedData['birthday'];
            }
    
            if (isset($validatedData['mobile_number'])) {
                $patientData['mobile_number'] = $validatedData['mobile_number'];
            }
            
            $patient= Patient::create($adminData);

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

    public function updatePatient(Request $request, $id) {
        try {
            // Find the patient by ID
            $patient = Patient::findOrFail($id);
    
            // Validate the incoming request data
            $request->validate([
                'first_name' => 'string',
                'last_name' => 'string',
                'middle_name' => 'string|nullable',
                'extension_name' => 'string|nullable',
                'birthday' => 'date|nullable',
                'sex' => 'in:Male,Female|nullable',
                'religion' => 'string|nullable',
                'home_address' => 'string|nullable',
                'home_phone_number' => 'string|nullable',
                'office_address' => 'string|nullable',
                'work_phone_number' => 'string|nullable',
                'mobile_number' => 'string|nullable',
                'marital_status' => 'string|nullable',
                'spouse' => 'string|nullable',
                'person_responsible_for_the_account' => 'string|nullable',
                'person_responsible_mobile_number' => 'string|nullable',
                'relationship' => 'string|nullable',
                'referal_person' => 'string|nullable',
            ]);
    
            // Update only the fields provided in the request
            $patient->update($request->all());
    
            return response()->json(['message' => 'Patient data updated successfully'], 200);
        } catch (ModelNotFoundException $e) {
            // Handle the case where the patient is not found
            return response()->json(['message' => 'Patient not found'], 404);
        } catch (\Exception $e) {
            // Handle other unexpected errors
            return response()->json(['message' => 'An unexpected error occurred'], 500);
        }
    }



    public function deletePatient($id)
    {
        try {
            // Find the patient by ID
            $patient = Patient::findOrFail($id);

            // Delete the patient
            $patient->delete();

            return response()->json(['message' => 'Patient deleted successfully'], 200);
        } catch (ModelNotFoundException $e) {
            // Handle the case where the patient is not found
            return response()->json(['message' => 'Patient not found'], 404);
        } catch (\Exception $e) {
            // Handle other unexpected errors
            return response()->json(['message' => 'An unexpected error occurred'], 500);
        }
    }
}
