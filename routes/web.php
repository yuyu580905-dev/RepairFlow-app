<?php

use App\Http\Controllers\CancellationRequestController;
use App\Http\Controllers\RepairRequestController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::view('/dashboard', 'dashboard')
    ->middleware('auth')
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/repair-requests', [
        RepairRequestController::class,
        'index',
    ])->name('repair-requests.index');

    Route::get('/repair-requests/create', [
        RepairRequestController::class,
        'create',
    ])->name('repair-requests.create');

    Route::post('/repair-requests', [
        RepairRequestController::class,
        'store',
    ])->name('repair-requests.store');

    Route::get('/repair-requests/{repairRequest}', [
        RepairRequestController::class,
        'show',
    ])->name('repair-requests.show');

    Route::patch('/repair-requests/{repairRequest}/technician', [
        RepairRequestController::class,
        'assignTechnician',
    ])->name('repair-requests.assign-technician');

    Route::patch('/repair-requests/{repairRequest}/status', [
        RepairRequestController::class,
        'updateStatus',
    ])->name('repair-requests.update-status');

    Route::post(
        '/repair-requests/{repairRequest}/cancellation-requests',
        [CancellationRequestController::class, 'store']
    )->name('repair-requests.cancellation-requests.store');

    Route::post(
        '/cancellation-requests/{cancellationRequest}/review',
        [CancellationRequestController::class, 'review']
    )->name('cancellation-requests.review');
});
