<?php

namespace App\Http\Controllers\API;

use App\Models\Services;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $services = Services::all();
        return response()->json($services);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'service_type' => 'required|string',
            'price' => 'required|numeric',
            'duration' => 'required|numeric',
        ]);
    
        try {
            $service = Services::create($validatedData);
            return response()->json(['message' => 'Service created successfully', 'service' => $service], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while creating the service'], 500);
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
        $service = Services::find($id);

        if (!$service) {
            return response()->json(['error' => 'Service not found'], 404);
        }

        return response()->json($service);
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
        $validatedData = $request->validate([
            'service_type' => 'sometimes|string',
            'price' => 'sometimes|numeric',
            'duration' => 'sometimes|numeric',
        ]);
    
        try {
            $service = Services::find($id);
    
            if (!$service) {
                return response()->json(['error' => 'Service not found'], 404);
            }
    
            $service->update($validatedData);
            return response()->json(['message' => 'Service updated successfully', 'service' => $service], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while updating the service'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $service = Services::find($id);
    
            if (!$service) {
                return response()->json(['error' => 'Service not found'], 404);
            }
    
            $service->delete();
            return response()->json(['message' => 'Service deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while deleting the service'], 500);
        }
    }
}
