<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\User;
use App\Models\Admin;
use Illuminate\Http\Request;
use App\Models\PatientImageRecord;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AdminController extends Controller
{
    /*
     * Display a listing of the resource.
     */
    public function index()
    {
        $admins = Admin::all();

        $admins->each(function ($admin) {
            $admin->email = $admin->user->email;
            $admin->user_type = $admin->user->user_type;

            unset($admin->user);

        });

        return response()->json(['data' => $admins], 200);
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
            'home_address' => 'required|string',
            'mobile_number' => 'sometimes|string',
        ]);

        try {
            DB::beginTransaction();

            $user = User::create([
                'email' => $validatedData['email'],
                'user_type' => "admin",
                'password' => Hash::make($validatedData['password']),
            ]);

            $adminData = [
                'user_id' => $user->id,
                'first_name' => $validatedData['first_name'],
                'last_name' => $validatedData['last_name'],
                'home_address' => $validatedData['home_address'],
            ];
    
         
            if (isset($validatedData['middle_name'])) {
                $adminData['middle_name'] = $validatedData['middle_name'];
            }
    
            if (isset($validatedData['extension_name'])) {
                $adminData['extension_name'] = $validatedData['extension_name'];
            }
    
            if (isset($validatedData['birthday'])) {
                $adminData['birthday'] = $validatedData['birthday'];
            }
    
            if (isset($validatedData['mobile_number'])) {
                $adminData['mobile_number'] = $validatedData['mobile_number'];
            }
    
            $admin = Admin::create($adminData);

            DB::commit();

            return response()->json(['message' => 'Admin User has been Created'], 201);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => $e->message], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
        
            $admin = Admin::findOrFail($id);
            
            $user = $admin->user;

            unset($admin->user);
            
            return response()->json([
                'user' => $user,
                'admin' => $admin
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
            'email' => 'nullable|email',
            'password' => 'nullable|string',
            'first_name' => 'nullable|string',
            'last_name' => 'nullable|string',
            'middle_name' => 'nullable|string',
            'extension_name' => 'nullable|string',
            'birthday' => 'nullable|date',
            'home_address' => 'nullable|string',
            'mobile_number' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $admin = Admin::findOrFail($id);

            if (isset($validatedData['email'])) {
                $admin->user->email = $validatedData['email'];
                $admin->user->save();
            }

            $admin->update($validatedData);

            DB::commit();

            return response()->json(['message' => 'Admin updated successfully'], 200);
        } catch (ModelNotFoundException $e) {

            DB::rollBack();
            return response()->json(['message' => 'Admin not found'], 404);
        } catch (ValidationException $e) {

            DB::rollBack();
            $errors = $e->validator->getMessageBag();

            if ($errors->has('email')) {
                return response()->json(['message' => 'Email already in use'], 422);
            }

            return response()->json(['message' => 'Validation failed', 'errors' => $errors], 422);
        } catch (QueryException $e) {

            DB::rollBack();
            return response()->json(['message' => 'Database error occurred', 'error' => $e->getMessage()], 500);

        } catch (Exception $e) {

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

            $admin = Admin::findOrFail($id);

            $userId = $admin->user_id;

            $admin->delete();

            User::where('id', $userId)->delete();

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


    public function addPatientImages(Request $request, $id) {
        $validatedData = $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
          ]);

          try {

            DB::beginTransaction();

            
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('patient_images'), $imageName);
                $imagePath = 'patient_images/' . $imageName; // Relative path to the image
              
                $imageRecord = [
                  'patient_id' => $id,
                  'image_type' => 'Documents',
                  'image_path' => $imagePath,
                ];
              
                PatientImageRecord::create($imageRecord);
              }

            DB::commit();

            return response()->json(['message' => 'Image successfully Uploaded'], 201);
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
            return response()->json(['message' => $e], 500);
        }
    }
}
