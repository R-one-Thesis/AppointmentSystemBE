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

        $patients->each(function ($patient) {
            $patient->email = $patient->user->email;
            $patient->user_type = $patient->user->user_type;

            unset($patient->user);

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
            'medication' => 'sometimes|array',
            'allergies' => 'sometimes|array',
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
            
            $patientHistoryData = ['user_id' => $patient->id];
            
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

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
