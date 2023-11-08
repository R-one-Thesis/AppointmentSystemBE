<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Booking;
use App\Models\Patient;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ScheduleController extends Controller
{
    public function viewSchedules() {

        $allSchedules = Schedule::all();

        if ($allSchedules->isEmpty()) {
            return response()->json(['message' => 'No data found']);
        }
        $schedules = $allSchedules->map(function($data){
            return [
                "id" => $data->id,
                "doctors_id" => $data->doctors_id,
                "dentist_name" => $data->doctor->dentist,
                "specialization" => $data->doctor->specialization,
                "services" => $data->services,
                "date" => $data->date,
                "time_start" => $data->time_start,
                "duration" => $data->duration,
                "booked" => $data->booked,
                
            ];
        });    

        return response()->json(['message' => 'data found', 'schedules' => $schedules]);
    }

    public function addSchedule(Request $request) {
        $validator = Validator::make($request->all(), [
            'doctors_id' => 'required',
            'services' => 'required|array',
            'date' => 'required|date',
            'time_start' => 'required|date_format:H:i',
            'duration' => 'required',
        ]);
        Log::info('Input data: ' . json_encode($request->all()));
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }
    
        try {
            $scheduleData = $request->all();
            $scheduleData['booked'] = false;
            
            // No need to convert 'services' to JSON here, as it's already cast as 'array' in the model.
            // Schedule::create($scheduleData);
    
            // Use this line instead:
            Schedule::create([
                'doctors_id' => $scheduleData['doctors_id']['doctors_id'],
                'services' => $scheduleData['services'],
                'date' => $scheduleData['date'],
                'time_start' => $scheduleData['time_start'],
                'duration' => $scheduleData['duration']['duration'],
                'booked' => false,
            ]);
            
            return response()->json(['message' => 'Schedule added successfully'], 201);
        } catch (Exception $e) {
            return response()->json(['error' => 'An error occurred while adding the schedule'], 500);
        }
    }

    public function bookSchedule($id)
    {
        // Get the authenticated user's ID
        $patientId = Patient::select('id')
                    ->where('user_id', '=',auth()->user()->id)
                    ->first();

        try {
            DB::beginTransaction();

            $schedule = Schedule::lockForUpdate()->findOrFail($id);

            if ($schedule->booked == true) {
                DB::rollBack();
                return response()->json(['message' => 'Schedule is already booked'], 400);
            }

            $booking = new Booking([
                'patient_id' => $patientId->id,
                'schedule_id' => $id,
            ]);

            $booking->save();
            
            $schedule->update(['booked' => true]);

            DB::commit();

            return response()->json(['message' => 'Booking successful'], 201);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json(['message' => 'Schedule not found'], 404);
        } catch (ValidationException $e) {
            DB::rollBack();
            $errors = $e->validator->getMessageBag();

            // Handle validation errors here
            return response()->json(['message' => 'Validation failed', 'errors' => $errors], 422);
        } catch (QueryException $e) {
            DB::rollBack();
            return response()->json(['message' => 'Database error occurred', 'error' => $e->getMessage()], 500);
        } catch (error) {
            DB::rollBack();
            return response()->json(['message' => error], 500);
        }
    }

    
}
