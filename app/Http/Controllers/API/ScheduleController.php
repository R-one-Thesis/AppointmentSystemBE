<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Booking;
use App\Models\Patient;
use App\Models\Schedule;
use App\Models\Services;
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
            'date' => 'required|date',
            'time_start' => 'required|date_format:H:i',
            
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
                'date' => $scheduleData['date'],
                'time_start' => $scheduleData['time_start'],
                'booked' => false,
            ]);
            
            return response()->json(['message' => 'Schedule added successfully'], 201);
        } catch (Exception $e) {
            return response()->json(['error' => 'An error occurred while adding the schedule'], 500);
        }
    }

    public function bookSchedule(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'services' => 'required|array',
            'services.*' => 'exists:services,id', // Check if each service ID exists in the services table
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();
            $schedule = Schedule::lockForUpdate()->findOrFail($id);

            if ($schedule->booked) {
                DB::rollBack();
                return response()->json(['message' => 'Schedule is already booked'], 400);
            }

            // Get the authenticated user's ID
            $patientId = Patient::where('user_id', auth()->user()->id)->value('id');

            $serviceTypeArray = Services::whereIn('id', $request->input('services'))->pluck('service_type')->toArray();

            $schedule->services = $serviceTypeArray; // Assign selected service types to the schedule
            $schedule->duration = Services::whereIn('id', $request->input('services'))->sum('duration'); // Calculate total duration

            $booking = new Booking([
                'patient_id' => $patientId,
                'schedule_id' => $id,
            ]);

            $booking->save();
            
            $schedule->booked = true;
            $schedule->save();
            $patientMobileNumber = Patient::where('user_id', auth()->user()->id)->value('mobile_number');
            if(isset($patientMobileNumber)) {

                $BASE_URL = "https://4384e8.api.infobip.com";
                $API_KEY = "c23190f9a71488ae4973dcdd8b17962a-86aa644a-f873-4af8-b975-04e39a97deca";
                $SENDER = "InfoSMS";
                $MESSAGE_TEXT = "Booking successful! Your schedule details here..."; // Customize your message
        
                $configuration = new Configuration(host: $BASE_URL, apiKey: $API_KEY);
                $sendSmsApi = new SmsApi(config: $configuration);
        
                $destination = new SmsDestination(
                    to: $patientMobileNumber
                );
        
                $message = new SmsTextualMessage(destinations: [$destination], from: $SENDER, text: $MESSAGE_TEXT);
                $request = new SmsAdvancedTextualRequest(messages: [$message]);
        
                // Send SMS
                $smsResponse = $sendSmsApi->sendSmsMessage($request);
        
                // Handle SMS response if needed
                // For example:
                if ($smsResponse) {
                    // SMS sent successfully
                    // You might want to log this or perform additional actions
                } else {
                    // SMS sending failed
                    // Log or handle the failure accordingly
                }
                
            }

            DB::commit();

            return response()->json(['message' => 'Booking successful'], 201);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json(['message' => 'Schedule not found'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'An error occurred', 'error' => $e->getMessage()], 500);
        }
    }


    
}
