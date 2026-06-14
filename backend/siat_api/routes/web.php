<?php

use App\Http\Controllers\Admin\AdminPageController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminPageController::class, 'dashboard'])->name('dashboard');
    Route::get('/students', [AdminPageController::class, 'students'])->name('students');
    Route::get('/lecturers', [AdminPageController::class, 'lecturers'])->name('lecturers');
    Route::get('/approvals', [AdminPageController::class, 'approvals'])->name('approvals');
    Route::get('/audit-logs', [AdminPageController::class, 'auditLogs'])->name('audit-logs');
});
