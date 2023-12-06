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

    public function viewTodaysSchedules() {
        $allSchedules = Schedule::where('date', date('Y-m-d'))->get();
    
        if ($allSchedules->isEmpty()) {
            return response()->json(['message' => 'No schedules found for this date']);
        }
    
        $schedules = $allSchedules->map(function($data){
            return [
                "id" => $data->id,
                "doctors_id" => $data->doctors_id,
                "dentist_name" => $data->doctor->dentist,
                "services" => $data->services,
                "date" => $data->date,
                "time_start" => $data->time_start,
                "duration" => $data->duration,
                "price" => $data->price,
                "booked" => $data->booked,
            ];
        });    
    
        return response()->json(['message' => 'Schedules found for the date', 'schedules' => $schedules]);
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

        $doctorId = $request->input('doctors_id');
        $date = $request->input('date');
        $timeStart = $request->input('time_start');

        // Check if a schedule already exists for the same doctor, date, and time_start
        $existingSchedule = Schedule::where('doctors_id', $doctorId)
            ->where('date', $date)
            ->where('time_start', $timeStart)
            ->first();

        if ($existingSchedule) {
            return response()->json(['error' => 'A schedule already exists for this doctor, date, and time'], 400);
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

    // public function bookSchedule(Request $request, $id)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'services' => 'required|array',
    //         'services.*' => 'exists:services,id', // Check if each service ID exists in the services table
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
    //     }

    //     try {
    //         DB::beginTransaction();
    //         $schedule = Schedule::lockForUpdate()->findOrFail($id);

    //         if ($schedule->booked) {
    //             DB::rollBack();
    //             return response()->json(['message' => 'Schedule is already booked'], 400);
    //         }

    //         if ($schedule->booking->) {
    //             DB::rollBack();
    //             return response()->json(['message' => 'Schedule is already booked'], 400);
    //         }

    //         // Get the authenticated user's ID
    //         $patientId = Patient::where('user_id', auth()->user()->id)->value('id');

    //         $serviceTypeArray = Services::whereIn('id', $request->input('services'))->pluck('service_type')->toArray();

    //         $schedule->services = $serviceTypeArray; // Assign selected service types to the schedule
    //         $schedule->duration = Services::whereIn('id', $request->input('services'))->sum('duration'); // Calculate total duration
    //         $schedule->price = Services::whereIn('id', $request->input('services'))->sum('price');

    //         $booking = new Booking([
    //             'patient_id' => $patientId,
    //             'schedule_id' => $id,
    //         ]);

    //         $booking->save();
            
            
    //         $schedule->save();
    //         $patientMobileNumber = Patient::where('user_id', auth()->user()->id)->value('mobile_number');
    //         if (strpos($patientMobileNumber, '0') === 0) {
    //             // Mobile number starts with '0'
    //             $patientMobileNumber = '+63' . substr($patientMobileNumber, 1); // Replace '0' with '+63'
    //         }
           
    //         DB::commit();

            
    //         if(isset($patientMobileNumber)){
    //             try {
    //                 $receiverNumber = $patientMobileNumber;
    //                 $scheduleDetails = "Thank you for booking with us!\nSchedule Details:\nDate: " . $schedule->date . "\nTime: " . $schedule->time_start . "\nDuration: " . $schedule->duration . " minutes\nPrice: ₱" . $schedule->price;

    //                 $message = "Dear Customer, \n\n" . $scheduleDetails . "\n\nWe look forward to seeing you!";



    //                 $account_sid = env('TWILIO_SID', 'AC5606baee61946654be8421769d330238');
    //                 $auth_token = env('TWILIO_TOKEN', 'f8ccf7a86475ef87cad7dac06ad74fad');
    //                 $twilio_number = env('TWILIO_FROM', '+16092566441');


    //                 $client = new Client($account_sid, $auth_token);
    //                 $client->messages->create($receiverNumber, [
    //                     'from' => $twilio_number, 
    //                     'body' => $message]);
        
    //                 $smsMessage = 'SMS Sent Successfully.';
        
    //             } catch (Exception $e) {
                    
    //                 $smsMessage = $e->getMessage();
    //             }
    //         }
            

    //         return response()->json(['message' => 'Booking successful', 'sms-msg' => $smsMessage], 201);
    //     } catch (ModelNotFoundException $e) {
    //         DB::rollBack();
    //         return response()->json(['message' => 'Schedule not found'], 404);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json(['message' => 'An error occurred', 'error' => $e->getMessage()], 500);
    //     }
    // }

    function bookSchedule(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'services' => 'required|array',
            'services.*' => 'exists:services,id', // Check if each service ID exists in the services table
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $schedule = Schedule::lockForUpdate()->findOrFail($id);

        if ($schedule->booked) {
            return response()->json(['message' => 'Schedule is already booked'], 400);
        }

        $allFalse = Booking::where('schedule_id', $id)
            ->get()
            ->every(fn ($booking) => $booking->approved === false);

        $bookingCount = Booking::where('schedule_id', $id)->count();

        if ($allFalse || $bookingCount === 0) {
            try {
                DB::beginTransaction();

                // Booking creation logic...
                $patientId = Patient::where('user_id', auth()->user()->id)->value('id');
                $serviceTypeArray = Services::whereIn('id', $request->input('services'))->pluck('service_type')->toArray();
                $serializedServices = json_encode($serviceTypeArray);
                
                $booking = new Booking([
                    'patient_id' => $patientId,
                    'schedule_id' => $id,
                    'services' => $serializedServices,
                    'price' => Services::whereIn('id', $request->input('services'))->sum('price'),
                    'duration' => Services::whereIn('id', $request->input('services'))->sum('duration'),
                ]);
                $booking->save();

                // SMS logic...
                $patientMobileNumber = Patient::where('user_id', auth()->user()->id)->value('mobile_number');

                if (strpos($patientMobileNumber, '0') === 0) {
                    // Mobile number starts with '0'
                    $patientMobileNumber = '+63' . substr($patientMobileNumber, 1); // Replace '0' with '+63'
                }

                // Ensure $patientMobileNumber is set before proceeding
                if (isset($patientMobileNumber)) {
                    $receiverNumber = $patientMobileNumber;
                    $scheduleDetails = "Thank you for booking with us!\nSchedule Details:\nDate: " . $schedule->date . "\nTime: " . $schedule->time_start . "\nDuration: " . $schedule->duration . " minutes\nPrice: ₱" . $schedule->price;

                    $message = "Dear Customer, \n\n" . $scheduleDetails . "\n\nwait for the confirmation message that will be sent to you!";

                    // Your Twilio configuration and sending logic
                    $account_sid = env('TWILIO_SID', 'your_twilio_sid');
                    $auth_token = env('TWILIO_TOKEN', 'your_twilio_token');
                    $twilio_number = env('TWILIO_FROM', 'your_twilio_number');

                    $client = new Client($account_sid, $auth_token);
                    $client->messages->create($receiverNumber, [
                        'from' => $twilio_number, 
                        'body' => $message
                    ]);

                    $smsMessage = 'SMS Sent Successfully.';
                }

                DB::commit();

                return response()->json(['message' => 'Booking successful', 'sms-msg' => $smsMessage ?? null], 201);
            } catch (ModelNotFoundException $e) {
                DB::rollBack();
                return response()->json(['message' => 'Schedule not found'], 404);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['message' => 'An error occurred', 'error' => $e->getMessage()], 500);
            }
        } else {
            return response()->json(['message' => 'The schedule has pending bookings'], 400);
        }
    }

    public function approveBooking($id)
    {
        try {
            DB::beginTransaction();

            $booking = Booking::findOrFail($id);
            
            if ($booking->approved !== null) {
                return response()->json(['message' => 'Booking has already been approved or rejected'], 400);
            }

            // Check if schedule is null
            $schedule = Schedule::where('id', '=', $booking->schedule_id)->first();
            if (!$schedule) {
                return response()->json(['message' => 'Schedule not found for the booking'], 400);
            }

            // Update schedule columns based on the booking
            $schedule->services = $booking->services;
            $schedule->duration = $booking->duration;
            $schedule->price = $booking->price;
            $schedule->booked = true; // Assuming you want to mark the schedule as booked upon approval
            $schedule->save();

            // Update the booking's approved status
            $booking->approved = true;
            $booking->save();

            // SMS logic...
            $patientMobileNumber = Patient::where('user_id', $booking->patient->user_id)->value('mobile_number');

            if (strpos($patientMobileNumber, '0') === 0) {
                // Mobile number starts with '0'
                $patientMobileNumber = '+63' . substr($patientMobileNumber, 1); // Replace '0' with '+63'
            }

            // Ensure $patientMobileNumber is set before proceeding
            if (isset($patientMobileNumber)) {
                $receiverNumber = $patientMobileNumber;
                $scheduleDetails = "Your booking has been approved!\nSchedule Details:\nDate: " . $schedule->date . "\nTime: " . $schedule->time_start . "\nDuration: " . $schedule->duration . " minutes\nPrice: ₱" . $schedule->price;

                $message = "Dear Customer, \n\n" . $scheduleDetails;

                // Your Twilio configuration and sending logic
                $account_sid = env('TWILIO_SID', 'AC9d35d9ac6d7860a9b83dd96a1e8e9719');
                $auth_token = env('TWILIO_TOKEN', '22430a936dd96c85e43908a7cc156753');
                $twilio_number = env('TWILIO_FROM', '+14422449111');

                $client = new Client($account_sid, $auth_token);
                $client->messages->create($receiverNumber, [
                    'from' => $twilio_number, 
                    'body' => $message
                ]);

                $smsMessage = 'SMS Sent Successfully.';
            }

            DB::commit();

            return response()->json(['message' => 'Booking approved successfully', 'sms-msg' => $smsMessage ?? null], 200);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json(['message' => 'Booking not found'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'An error occurred', 'error' => $e->getMessage()], 500);
        }
    }

    public function rejectBooking($bookingId)
    {
        try {
            DB::beginTransaction();

            $booking = Booking::findOrFail($bookingId);
            
            if ($booking->approved !== null) {
                return response()->json(['message' => 'Booking has already been approved or rejected'], 400);
            }

            // Update the booking's approved status to false (rejected)
            $booking->approved = false;
            $booking->save();

            // SMS logic for rejection...
            $patientMobileNumber = Patient::where('user_id', $booking->patient->user_id)->value('mobile_number');

            if (strpos($patientMobileNumber, '0') === 0) {
                // Mobile number starts with '0'
                $patientMobileNumber = '+63' . substr($patientMobileNumber, 1); // Replace '0' with '+63'
            }

            // Ensure $patientMobileNumber is set before proceeding
            if (isset($patientMobileNumber)) {
                $receiverNumber = $patientMobileNumber;
                $message = "Dear Customer, \n\nSorry, but your booking has been rejected.";

                // Your Twilio configuration and sending logic
                $account_sid = env('TWILIO_SID', 'AC9d35d9ac6d7860a9b83dd96a1e8e9719');
                $auth_token = env('TWILIO_TOKEN', '22430a936dd96c85e43908a7cc156753');
                $twilio_number = env('TWILIO_FROM', '+14422449111');

                $client = new Client($account_sid, $auth_token);
                $client->messages->create($receiverNumber, [
                    'from' => $twilio_number, 
                    'body' => $message
                ]);

                $smsMessage = 'SMS Sent Successfully.';
            }

            DB::commit();

            return response()->json(['message' => 'Booking rejected successfully', 'sms-msg' => $smsMessage ?? null], 200);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json(['message' => 'Booking not found'], 404);
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
