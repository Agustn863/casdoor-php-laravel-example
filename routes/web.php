<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [AuthController::class, 'redirectToCasdoor'])->name('login');
Route::get('/callback', [AuthController::class, 'callback'])->name('callback');
Route::get('/user-info', [AuthController::class, 'getUserInfo'])->name('user.info');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
