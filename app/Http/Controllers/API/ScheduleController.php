<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Schedule;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ScheduleController extends Controller
{
    public function viewSchedules() {

        $schedules = Schedule::all();

        if ($schedules->isEmpty()) {
            return response()->json(['message' => 'No data found']);
        }    

        return response()->json(['message' => 'data found', 'schedules' => $schedules]);
    }

    public function addSchedule(Request $request) {
        $validator = Validator::make($request->all(), [
            'doctors_id' => 'required|exists:doctors,id',
            'services' => 'required|json',
            'date' => 'required|date',
            'time_start' => 'required|date_format:H:i',
            'duration' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        try {
            $scheduleData = $request->all();
            $scheduleData['booked'] = false;
            Schedule::create($scheduleData);
            return response()->json(['message' => 'Schedule added successfully'], 201);
        } catch (Exception $e) {
            return response()->json(['error' => 'An error occurred while adding the schedule'], 500);
        }
    }
}
