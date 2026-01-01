<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/* Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum'); */
Route::post('/register',[AuthController::class,'register']);
Route::post('/login',[AuthController::class,'login']);

Route::middleware('auth:sanctum')->group(function(){
    Route::post('/logout', [AuthController::class,'logout']);
    Route::get('/wallet', [WalletController::class,'wallet']);
    Route::get('/transactions', [WalletController::class,'transactions']);
    Route::post('/topup',[WalletController::class,'topup']);
    Route::post('/transfer',[WalletController::class,'transfer']);
});
