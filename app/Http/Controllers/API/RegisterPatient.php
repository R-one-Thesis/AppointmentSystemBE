<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\User;
use App\Models\History;
use App\Models\Patient;
use App\Models\PatientImageRecord;
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
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
          ]);
        Log::info('Input data: ' . json_encode($request->all()));

        try {

            DB::beginTransaction();

            $user = User::create([
                'email' => $validatedData['email'],
                'user_type' => "patient",
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
            
            $patient= Patient::create($patientData);

            $patientHistoryData = ['patient_id' => $patient->id];
            $patientHistory = History::create($patientHistoryData);
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('patient_images'), $imageName);
                $imagePath = 'patient_images/' . $imageName; // Relative path to the image
              
                $imageRecord = [
                  'patient_id' => $patient->id,
                  'image_type' => 'ID',
                  'image_path' => $imagePath,
                ];
              
                PatientImageRecord::create($imageRecord);
              }

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
