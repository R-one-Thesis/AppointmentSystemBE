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
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PatientController extends Controller
{
    /*
     * Display a listing of the resource.
     */
    public function index()
    {
        $patients = Patient::all();

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
            $imageRecords = [];
            if ($patient->patientImageRecord) {
                $imageRecords = $patient->patientImageRecord->map(function ($record) {
                    return [
                        'image_type' => $record->image_type,
                        'image_path' => $record->image_path,
                    ];
                });
            }
        
            $patient->image_records = $imageRecords->toArray();

            unset($patient->user,$patient->history, $patient->patientImageRecord);

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
            'pregnant' => 'sometimes|boolean',
            'expected_date' => 'sometimes|date',
            'mens_problems' => 'sometimes|boolean',
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            
        ]);

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
                'expected_date', 'mens_problems'
            ];
            
            $patientHistoryData = ['patient_id' => $patient->id];
            
            foreach ($historyFields as $field) {
                if (isset($validatedData[$field])) {
                    $patientHistoryData[$field] = $validatedData[$field];
                }
            }
            
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
            
            DB::rollBack();
    
            $errors = $e->validator->getMessageBag();
            
            if ($errors->has('email')) {
                
                return response()->json(['message' => 'Email already in use'], 422);
            }
    
            
            return response()->json(['message' => 'Validation failed', 'errors' => $errors], 422);
        } 
        catch (QueryException $e) {
           
            DB::rollBack();
            return response()->json(['message' => 'Database error occurred', 'error' => $e->getMessage()], 500);
        } catch (Exception $e) {
            
            DB::rollBack();
            return response()->json(['message' => 'An unexpected error occurred'], 500);
        }
    }

    /*
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
        
            $patient = Patient::findOrFail($id);
            
            $user = $patient->user;

            
            $history = $patient->history;
            $imageRecords = [];
            if ($patient->patientImageRecord) {
                $imageRecords = $patient->patientImageRecord->map(function ($record) {
                    return [
                        'image_type' => $record->image_type,
                        'image_path' => $record->image_path,
                    ];
                });
            }
        
            $patient->image_records = $imageRecords->toArray();
            unset($patient->user, $patient->history,  $patient->patientImageRecord);
            
            return response()->json([
                'user' => $user,
                'patient' => $patient,
                'history' => $history
            ], 200);
        } catch (ModelNotFoundException $e) {
            
            return response()->json(['message' => 'Patient not found'], 404);
        } catch (\Exception $e) {
           
            return response()->json(['message' => 'An unexpected error occurred'], 500);
        }
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
            'pregnant' => 'nullable|boolean',
            'expected_date' => 'nullable|date',
            'mens_problems' => 'nullable|boolean',
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

            $patient = Patient::findOrFail($id);

            $userId = $patient->user_id;

            $patient->delete();

            User::where('id', $userId)->delete();

            History::where('id', $userId)->delete();

            DB::commit();

            return response()->json(['message' => 'Patient and associated user deleted successfully'], 200);
        } catch (ModelNotFoundException $e) {

            DB::rollBack();
            return response()->json(['message' => 'Patient not found'], 404);

        } catch (\Exception $e) {

            DB::rollBack();
            return response()->json(['message' => 'An unexpected error occurred'], 500);
            
        }
    }   


}
