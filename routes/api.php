<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ResumeController;
use App\Http\Controllers\WeatherController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::group(['prefix' => 'v1'], function () {
    Route::post('register', [RegisterController::class, 'register']);
    Route::post('get_user_data', [ResumeController::class, 'get_user_data']);
    Route::post('update', [ResumeController::class, 'update']);
    Route::post('login', [LoginController::class, 'login']);
    Route::get('get_weather', [WeatherController::class, 'index']);
});
