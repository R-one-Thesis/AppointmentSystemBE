<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Booking;
use App\Models\Patient;
use Twilio\Rest\Client;
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
                "price" => $data->price,
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
            $schedule->price = Services::whereIn('id', $request->input('services'))->sum('price');

            $booking = new Booking([
                'patient_id' => $patientId,
                'schedule_id' => $id,
            ]);

            $booking->save();
            
            $schedule->booked = true;
            $schedule->save();
            $patientMobileNumber = Patient::where('user_id', auth()->user()->id)->value('mobile_number');
           
            DB::commit();

            
            if(isset($patientMobileNumber)){
                try {
                    $receiverNumber = $patientMobileNumber;
                    $scheduleDetails = "Thank you for booking with us!\nSchedule Details:\nDate: " . $schedule->date . "\nTime: " . $schedule->time_start . "\nDuration: " . $schedule->duration . " minutes\nPrice: $" . $schedule->price;

                    $message = "Dear Customer, \n\n" . $scheduleDetails . "\n\nWe look forward to seeing you!";



                    $account_sid = getenv("TWILIO_SID");
                    $auth_token = getenv("TWILIO_TOKEN");
                    $twilio_number = getenv("TWILIO_FROM");
        
                    $client = new Client($account_sid, $auth_token);
                    $client->messages->create($receiverNumber, [
                        'from' => $twilio_number, 
                        'body' => $message]);
        
                    $smsMessage = 'SMS Sent Successfully.';
        
                } catch (Exception $e) {
                    
                    $smsMessage = $e->getMessage();
                }
            }
            

            return response()->json(['message' => 'Booking successful', 'sms-msg' => $smsMessage ], 201);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json(['message' => 'Schedule not found'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'An error occurred', 'error' => $e->getMessage()], 500);
        }
    }

    public function getSchedule($id) {
        $schedule = Schedule::find($id);
    
        if (!$schedule) {
            return response()->json(['message' => 'No data found']);
        }
    
        $scheduleDetails = [
            "id" => $schedule->id,
            "doctors_id" => $schedule->doctors_id,
            "dentist_name" => $schedule->doctor->dentist,
            "specialization" => $schedule->doctor->specialization,
            "services" => $schedule->services,
            "date" => $schedule->date,
            "time_start" => $schedule->time_start,
            "duration" => $schedule->duration,
            "price" => $schedule->price,
            "booked" => $schedule->booked,
            "bookings" => $schedule->booking->map(function($booking) {
                return [
                    "id" => $booking->id,
                    "patient_id" => $booking->patient_id,
                    "patient_name" => $booking->patient->first_name . ' ' . $booking->patient->last_name,
                    // Add other patient information if needed
                ];
            }),
        ];
    
        return response()->json(['message' => 'Data found', 'schedule' => $scheduleDetails]);
    }
    


    
}
