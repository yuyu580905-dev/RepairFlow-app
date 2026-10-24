
<?php

use App\Http\Controllers\RepairRequestController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::view('/dashboard', 'dashboard')
    ->middleware('auth')
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/repair-requests/create', [RepairRequestController::class, 'create'])->name('repair-requests.create');

    Route::post('/repair-requests', [RepairRequestController::class, 'store'])->name('repair-requests.store');
});
