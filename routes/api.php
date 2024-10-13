<?php

use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:api');
  
//   Route::get('/get-user', [AuthController::class, 'user']);

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);