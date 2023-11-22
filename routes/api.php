<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

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




Route::group(['middleware' =>'api'],function ($routes){
    //register api
    Route::post('/register',[UserController::class,'register']);

    //sent otp api to email
    Route::post('/send-otp',[UserController::class,'sendOtp']);

    //verified
    Route::post('/verified',[UserController::class,'verifiedEmail']);

    //forget password api
    Route::post('/forget-password',[UserController::class,'forgetPassword']);

    //login with token
    Route::post('/login',[UserController::class,'login']);
    //user profile api
    Route::post('/profile',[UserController::class,'profile']);
    //add new password api
    Route::post('/update-password',[UserController::class,'updatePassword']);
    //regenerate token api
    Route::post('/refresh',[UserController::class,'refresh']);
    //logout api
    Route::post('/logout',[UserController::class,'logout']);
});
