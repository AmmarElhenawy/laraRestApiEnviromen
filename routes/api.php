<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

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

// Public Auth Routes
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

// Protected Routes
Route::middleware('jwt')->group(function () {
    Route::get('user-profile', [AuthController::class, 'userProfile']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);

    // Protected Post Routes
    Route::post('/post',[App\Http\Controllers\postController::class,'store']);
    Route::patch('/updatePost/{id}',[App\Http\Controllers\postController::class,'update']);
    Route::post('/deletePost/{id}',[App\Http\Controllers\postController::class,'destroy']);
});

// Public Post Routes
Route::get('/post',[App\Http\Controllers\postController::class,'index']);
Route::get('/post/{id}',[App\Http\Controllers\postController::class,'show']);
