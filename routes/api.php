<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\V1\Token\TokenApiController;
use App\Http\Controllers\API\V1\User\UserApiController;
use App\Http\Controllers\API\V1\Position\PositionController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::group([
    'prefix' => '/v1',
], function () {
    Route::get('/token', [TokenApiController::class, 'create'])->name('get-token');
    Route::group(['middleware' => 'jwt'], function () {
        Route::post('/users', [UserApiController::class, 'create'])->name('create-user-api');
    });
    Route::get('/users', [UserApiController::class, 'index'])->name('get-users');
    Route::get('/users/{userId}', [UserApiController::class, 'show'])->name('show-user');
    Route::get('/positions', [PositionController::class, 'index'])->name('get-positions');
});
