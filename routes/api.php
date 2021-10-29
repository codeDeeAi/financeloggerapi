<?php

use App\Http\Controllers\Accounts\AccountController;
use App\Http\Controllers\Authentication\AuthController;
use App\Http\Controllers\Transactions\TransactionController;
use App\Http\Controllers\User\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// Guests Routes


// Authentication
Route::post('/signup', [UserController::class, 'store']);
Route::post('/signin', [AuthController::class, 'login']);


Route::middleware(['auth:sanctum'])->group(function () {
    // Dashboard

    // Accounts 
    Route::get('/accounts', [AccountController::class, 'index']);
    Route::get('/account/{id}', [AccountController::class, 'show']);
    Route::post('/account', [AccountController::class, 'store']);
    Route::put('/account/{id}', [AccountController::class, 'update']);
    Route::delete('/account/{id}', [AccountController::class, 'destroy']);

    // Transactions
    Route::get('/transactions/{account_id}', [TransactionController::class, 'index']);
    Route::post('/transaction/{account_id}', [TransactionController::class, 'store']);
    Route::post('/charts/{account_id}', [TransactionController::class, 'chart']);
    Route::delete('/transaction/{id}', [TransactionController::class, 'destroy']);

    // Log out
    Route::get('/logout', [AuthController::class, 'logout']);
});
