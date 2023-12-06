<?php

namespace App\Http\Controllers\API;

use App\Models\Booking;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BookingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $bookings = Booking::all();

        $mappedBookings = $bookings->map(function($booking) {
            return [
                'id' => $booking->id,
                'schedule_id' => $booking->schedule_id,
                'patient_id' => $booking->patient_id,
                'services' => $booking->services,
                'price' => $booking->price,
                'duration' => $booking->duration,
                'approved' => $booking->approved,
                'first_name' => $booking->patient->first_name,
                'middle_name' => $booking->patient->middle_name,
                'last_name' => $booking->patient->last_name,
                'extension_name' => $booking->patient->extension_name,
                // You can add more fields here as per your requirement
            ];
        });

        return response()->json(['bookings' => $mappedBookings], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
