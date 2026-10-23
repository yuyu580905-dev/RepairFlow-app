<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'dashboard')
    ->middleware('auth')
    ->name('dashboard');

Route::view('/dashboard', 'dashboard')
    ->middleware('auth')
    ->name('dashboard');
