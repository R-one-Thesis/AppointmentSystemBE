<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\RegisterPatient;
use App\Http\Controllers\API\DoctorController;
use App\Http\Controllers\API\ScheduleController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/login', [AuthController::class, 'login']);

Route::group(['middleware' => ['auth:sanctum']], function() {
    // Dentist or Doctor
    Route::get('view-doctors', [DoctorController::class, 'viewDoctors']);
    Route::post('add-doctors', [DoctorController::class, 'addDoctor']);
    Route::get('view-schedules', [ScheduleController::class, 'viewSchedules']);
    Route::post('add-schedule', [ScheduleController::class, 'addSchedule']);

    Route::post('register', [RegisterPatient::class, 'registerPatient']);
    Route::get('logout', [AuthController::class, 'logout']);
});
// Route::get('view-doctors', [DoctorController::class, 'viewDoctors']);
//     Route::post('add-doctors', [DoctorController::class, 'addDoctor']);
//     Route::get('view-schedules', [ScheduleController::class, 'viewSchedules']);
//     Route::post('add-schedule', [ScheduleController::class, 'addSchedule']);
//     Route::get('logout', [AuthController::class, 'logout']);