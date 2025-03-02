<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
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


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']); 
/*User APIs  **/
Route::apiResource('users', UserController::class);
//Route::middleware('auth:sanctum')->get('/users/{id}', [AuthController::class, 'show']);
Route::middleware('api')->group(function () {
    Route::apiResource('users', UserController::class);
});
/* Products APIs **/
Route::apiResource('products', ProductController::class);
/* Categories APIs **/
Route::apiResource('categories', CategoryController::class);
   
