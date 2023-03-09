<?php

use App\Http\Controllers\Api\AniListController;
use App\Http\Controllers\Api\AuthController;
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

Route::controller(AuthController::class)->group(function () {
    Route::post('/login', 'login');
    Route::post('/register', 'register');
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::controller(AniListController::class)->group(function () {
        Route::get('/list', 'showList');
        Route::get('/recently', 'showRecentlyAdded');
        Route::get('/recommendations', 'showRecommendations');
        Route::get('/highest', 'showHighestRating');
        Route::post('/create', 'create');
    });

    Route::controller(AuthController::class)->group(function () {
        Route::get('/user', 'getUser');
        Route::delete('/logout', 'logout');
        Route::delete('/account', 'deleteUser');
        Route::put('/profile', 'updateUserInfo');
        Route::post('/image', 'updateProfilePhoto');
        Route::put('/change', 'changePassword');
        Route::delete('/user', 'deleteUser');
    });
});
