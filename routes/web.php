<?php

use App\Http\Controllers\CampaignController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\SMTPController;
use App\Http\Controllers\SendController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SingleEmailController;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\UnsubscribeController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/track/open/{id}', [TrackingController::class, 'open'])->name('track.open');
Route::get('/track/click/{id}', [TrackingController::class, 'click'])->name('track.click');
Route::get('/unsubscribe/{email}', [UnsubscribeController::class, 'unsubscribe'])->name('unsubscribe');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::resource('contacts', ContactController::class)->except(['show']);
    Route::post('/contacts/bulk-delete', [ContactController::class, 'bulkDelete'])->name('contacts.bulk-delete');
    Route::resource('groups', GroupController::class)->only(['index', 'store', 'destroy']);
    Route::resource('campaigns', CampaignController::class)->except(['show']);
    Route::get('/campaigns/{campaign}/live-stats', [CampaignController::class, 'liveStats'])->name('campaigns.live-stats');
    Route::post('/campaigns/{campaign}/send', [SendController::class, 'sendNow'])->name('campaigns.send');
    Route::post('/campaigns/{campaign}/pause', [SendController::class, 'pause'])->name('campaigns.pause');
    Route::post('/campaigns/{campaign}/resume', [SendController::class, 'resume'])->name('campaigns.resume');
    Route::post('/campaigns/{campaign}/send-test-email', [CampaignController::class, 'sendTestEmail'])->name('campaigns.send-test-email');

    Route::get('/smtp', [SMTPController::class, 'index'])->name('smtp.index');
    Route::post('/smtp', [SMTPController::class, 'store'])->name('smtp.store');
    Route::get('/smtp/{smtp}/edit', [SMTPController::class, 'edit'])->name('smtp.edit');
    Route::put('/smtp/{smtp}', [SMTPController::class, 'update'])->name('smtp.update');
    Route::delete('/smtp/{smtp}', [SMTPController::class, 'destroy'])->name('smtp.destroy');
    Route::patch('/smtp/{smtp}/toggle', [SMTPController::class, 'toggle'])->name('smtp.toggle');
    Route::post('/smtp/{smtp}/test', [SMTPController::class, 'testConnection'])->name('smtp.test');
    Route::post('/smtp/{smtp}/send-test-email', [SMTPController::class, 'sendTestEmail'])->name('smtp.send-test-email');
    Route::post('/smtp/bulk-upload', [SMTPController::class, 'bulkUpload'])->name('smtp.bulk-upload');

    Route::get('/import', [ImportController::class, 'index'])->name('import.index');
    Route::post('/import', [ImportController::class, 'store'])->name('import.store');

    Route::get('/unsubscribes', [UnsubscribeController::class, 'index'])->name('unsubscribes.index');
    Route::get('/single-email', [SingleEmailController::class, 'create'])->name('single-email.create');
    Route::post('/single-email', [SingleEmailController::class, 'store'])->name('single-email.store');
    Route::get('/reports', [ReportsController::class, 'index'])->name('reports.index');

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');

    Route::middleware('admin')->group(function () {
        Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserManagementController::class, 'create'])->name('users.create');
        Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserManagementController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
