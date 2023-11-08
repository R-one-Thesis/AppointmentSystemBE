<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Models\History;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

class PatientController extends Controller
{
    /*
     * Display a listing of the resource.
     */
    public function index()
    {
        $patients = Patient::all();
        // $patientsData = $patients->map(function($patient){
        //     return [
        //         'physician' => ($patient->history->physician) ?? null,
        //     ];
        // });

        $patients->each(function ($patient) {
            $patient->email = $patient->user->email;
            $patient->user_type = $patient->user->user_type;
            if ($patient->history) {
                $patient->physician = $patient->history->physician ?? "";
                $patient->physaddress = $patient->history->physaddress ?? "";
                $patient->reason = $patient->history->reason ?? "";
                $patient->hospitalization_reason = $patient->history->hospitalization_reason ?? "";
                $patient->conditions = $patient->history->conditions ?? [];
                $patient->medication = $patient->history->medication ?? "";
                $patient->allergies = $patient->history->allergies ?? "";
                $patient->pregnant = $patient->history->pregnant ?? "";
                $patient->expected_date = $patient->history->expected_date ?? "";
                $patient->mens_problems = $patient->history->mens_problems ?? "";
            }

            unset($patient->user,$patient->history);

        });

        return response()->json(['data' => $patients], 200);
    }

    /*
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'middle_name' => 'sometimes|string',
            'extension_name' => 'sometimes|string',
            'birthday' => 'sometimes|date',
            'sex' => 'required|in:Male,Female',
            'religion' => 'sometimes|string',
            'home_address' => 'required|string',
            'home_phone_number' => 'sometimes|string',
            'office_address' => 'sometimes|string',
            'work_phone_number' => 'sometimes|string',
            'mobile_number' => 'sometimes|string',
            'marital_status' => 'sometimes|string',
            'spouse' => 'sometimes|string',
            'person_responsible_for_the_account' => 'sometimes|string',
            'person_responsible_mobile_number' => 'sometimes|string',
            'relationship' => 'sometimes|string',
            'referal_person' => 'sometimes|string',
            'physician' => 'sometimes|string',
            'physaddress' => 'sometimes|string',
            'reason' => 'sometimes|string',
            'hospitalization_reason' => 'sometimes|string',
            'conditions' => 'sometimes|array',
            'medication' => 'sometimes|string',
            'allergies' => 'sometimes|string',
            'pregnant' => 'sometimes|string',
            'expected_date' => 'sometimes|date',
            'mens_problems' => 'sometimes|string',
            
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
            
            $patientFields = [
                'middle_name', 'extension_name', 'birthday', 'religion', 
                'home_phone_number', 'office_address', 'work_phone_number', 
                'mobile_number', 'marital_status', 'referal_person', 
                'spouse', 'person_responsible_for_the_account', 
                'person_responsible_mobile_number', 'relationship'
            ];
            
            foreach ($patientFields as $field) {
                if (isset($validatedData[$field])) {
                    $patientData[$field] = $validatedData[$field];
                }
            }
            
            $patient = Patient::create($patientData);
            
            $historyFields = [
                'physician', 'physaddress', 'reason', 'hospitalization_reason', 
                'conditions', 'medication', 'allergies', 'pregnant', 
                'expected_datemens_problems', 'mens_problems'
            ];
            
            $patientHistoryData = ['patient_id' => $patient->id];
            
            foreach ($historyFields as $field) {
                if (isset($validatedData[$field])) {
                    $patientHistoryData[$field] = $validatedData[$field];
                }
            }
            
            $patientHistory = History::create($patientHistoryData);
            
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

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /*
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        
        $validatedData = $request->validate([
            'email' => 'nullable|email', // Email is optional
            'first_name' => 'nullable|string', // First name is optional
            'last_name' => 'nullable|string', // Last name is optional
            'middle_name' => 'nullable|string',
            'extension_name' => 'nullable|string',
            'birthday' => 'nullable|date',
            'sex' => 'nullable|in:Male,Female', // Sex is optional
            'religion' => 'nullable|string',
            'home_address' => 'nullable|string',
            'home_phone_number' => 'nullable|string',
            'office_address' => 'nullable|string',
            'work_phone_number' => 'nullable|string',
            'mobile_number' => 'nullable|string',
            'marital_status' => 'nullable|string',
            'spouse' => 'nullable|string',
            'person_responsible_for_the_account' => 'nullable|string',
            'person_responsible_mobile_number' => 'nullable|string',
            'relationship' => 'nullable|string',
            'referal_person' => 'nullable|string',
            'physician' => 'nullable|string',
            'physaddress' => 'nullable|string',
            'reason' => 'nullable|string',
            'hospitalization_reason' => 'nullable|string',
            'conditions' => 'nullable|array',
            'medication' => 'nullable|string',
            'allergies' => 'nullable|string',
            'pregnant' => 'nullable|string',
            'expected_date' => 'nullable|date',
            'mens_problems' => 'nullable|string',
        ]);
        
        try {
            DB::beginTransaction();

            
            $patient = Patient::findOrFail($id);
            
            if (isset($validatedData['email'])) {
                $patient->user->email = $validatedData['email'];
                $patient->user->save();
            }

            $patientFields = [
                'first_name', 'last_name', 'middle_name', 'extension_name', 'birthday', 'sex', 'religion', 'home_address',
                'home_phone_number', 'office_address', 'work_phone_number', 'mobile_number', 'marital_status', 'spouse', 
                'person_responsible_for_the_account', 'person_responsible_mobile_number', 'relationship', 'referal_person'
            ];

            foreach ($patientFields as $field) {
                if (isset($validatedData[$field])) {
                    $patient->$field = $validatedData[$field];
                }
            }
            $patient->save();
            
            $historyFields = [
                'physician', 'physaddress', 'reason', 'hospitalization_reason',
                'conditions', 'medication', 'allergies', 'pregnant',
                'expected_date', 'mens_problems',
            ];

            foreach ($historyFields as $field) {
                if (isset($validatedData[$field])) {
                    $patient->history->$field = $validatedData[$field];
                }
            }

            $patient->history->save();

            DB::commit();

            return response()->json(['message' => 'Patient updated successfully'], 200);
        } catch (ModelNotFoundException $e) {
            // Patient not found
            DB::rollBack();
            return response()->json(['message' => 'Patient not found'], 404);
        } catch (ValidationException $e) {
            // Validation errors
            DB::rollBack();
            $errors = $e->validator->getMessageBag();

            if ($errors->has('email')) {
                return response()->json(['message' => 'Email already in use'], 422);
            }

            return response()->json(['message' => 'Validation failed', 'errors' => $errors], 422);
        } catch (QueryException $e) {
            // Database query errors
            DB::rollBack();
            return response()->json(['message' => 'Database error occurred', 'error' => $e->getMessage()], 500);
        } catch (Exception $e) {
            // Other unexpected errors
            DB::rollBack();
            return response()->json(['message' => 'An unexpected error occurred'], 500);
        }
    }


    /*
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            // Find the patient by ID
            $patient = Patient::findOrFail($id);

            // Get the associated user ID
            $userId = $patient->user_id;

            // Delete the patient
            $patient->delete();

            // Delete the user using the user ID obtained from the patient
            User::where('id', $userId)->delete();

            // If you also need to delete the patient's history, do it here

            DB::commit();

            return response()->json(['message' => 'Patient and associated user deleted successfully'], 200);
        } catch (ModelNotFoundException $e) {
            // Handle the case where the patient is not found
            DB::rollBack();
            return response()->json(['message' => 'Patient not found'], 404);
        } catch (\Exception $e) {
            // Handle other unexpected errors
            DB::rollBack();
            return response()->json(['message' => 'An unexpected error occurred'], 500);
        }
    }   


}
