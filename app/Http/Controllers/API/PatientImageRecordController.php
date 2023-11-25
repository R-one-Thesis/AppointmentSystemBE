<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\PatientImageRecord;
use App\Http\Controllers\Controller;

class PatientImageRecordController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $imageRecords = PatientImageRecord::all();
        return response()->json($imageRecords);
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
            'patient_id' => 'required|exists:patients,id',
            'image_type' => 'required|string',
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048', // Example image validation rules
        ]);
    
        try {
            $image = $request->file('image');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->storeAs('patient_images', $imageName); // Example: Store images in 'patient_images' directory
    
            $imageRecord = [
                'patient_id' => $validatedData['patient_id'],
                'image_type' => $validatedData['image_type'],
                'image_path' => $imageName,
            ];
    
            PatientImageRecord::create($imageRecord);
            return response()->json(['message' => 'Patient image record created successfully'], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while creating the patient image record'], 500);
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
        $imageRecord = PatientImageRecord::find($id);

        if (!$imageRecord) {
            return response()->json(['error' => 'Image record not found'], 404);
        }

        return response()->json($imageRecord);
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
            'patient_id' => 'sometimes|exists:patients,id',
            'image_type' => 'sometimes|string',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg|max:2048', // Example image validation rules
        ]);
    
        try {
            $imageRecord = PatientImageRecord::find($id);
    
            if (!$imageRecord) {
                return response()->json(['error' => 'Image record not found'], 404);
            }
    
            // Update patient ID and image type if provided
            if (isset($validatedData['patient_id'])) {
                $imageRecord->patient_id = $validatedData['patient_id'];
            }
            if (isset($validatedData['image_type'])) {
                $imageRecord->image_type = $validatedData['image_type'];
            }
    
            // Update image if provided
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $image->storeAs('patient_images', $imageName); // Example: Store images in 'patient_images' directory
    
                // Delete the previous image from storage
                Storage::delete('patient_images/' . $imageRecord->image_path);
    
                $imageRecord->image_path = $imageName;
            }
    
            $imageRecord->save();
            return response()->json(['message' => 'Patient image record updated successfully', 'record' => $imageRecord], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while updating the patient image record'], 500);
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
            $imageRecord = PatientImageRecord::find($id);
    
            if (!$imageRecord) {
                return response()->json(['error' => 'Image record not found'], 404);
            }
    
            // Delete the associated image file from storage
            Storage::delete('patient_images/' . $imageRecord->image_path);
    
            $imageRecord->delete();
            return response()->json(['message' => 'Patient image record deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while deleting the patient image record'], 500);
        }
    }
}
