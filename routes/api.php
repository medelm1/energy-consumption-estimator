<?php

use App\Http\Controllers\ApplianceController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::get('/appliances', [ApplianceController::class, 'index']);
Route::get('/appliances/{id}', [ApplianceController::class, 'show']);
Route::post('/appliances', [ApplianceController::class, 'store']);
Route::put('/appliances/{id}', [ApplianceController::class, 'update']);
Route::delete('/appliances/{id}', [ApplianceController::class, 'destroy']);

Route::get('/settings', [SettingsController::class, 'index']);
Route::get('/settings/{key}', [SettingsController::class, 'show']);
Route::put('/settings', [SettingsController::class, 'updateMultiple']);
Route::put('/settings/{key}', [SettingsController::class, 'update']);
