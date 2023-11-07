<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\User;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
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
                'user_type' => "Admin",
                'password' => Hash::make($validatedData['password']), // Hash the password
            ]);

            $adminData = [
                'user_id' => $user->id,
                'first_name' => $validatedData['first_name'],
                'last_name' => $validatedData['last_name'],
                'home_address' => $validatedData['home_address'],
            ];
    
            // Check if optional fields are provided and add them conditionally
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
