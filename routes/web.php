<?php

use App\Http\Controllers\CampaignController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SMTPController;
use App\Http\Controllers\SendController;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\UnsubscribeController;
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
    Route::resource('groups', GroupController::class)->only(['index', 'store', 'destroy']);
    Route::resource('campaigns', CampaignController::class)->except(['show']);
    Route::post('/campaigns/{campaign}/send', [SendController::class, 'sendNow'])->name('campaigns.send');

    Route::get('/smtp', [SMTPController::class, 'index'])->name('smtp.index');
    Route::post('/smtp', [SMTPController::class, 'store'])->name('smtp.store');
    Route::get('/smtp/{smtp}/edit', [SMTPController::class, 'edit'])->name('smtp.edit');
    Route::put('/smtp/{smtp}', [SMTPController::class, 'update'])->name('smtp.update');
    Route::delete('/smtp/{smtp}', [SMTPController::class, 'destroy'])->name('smtp.destroy');
    Route::patch('/smtp/{smtp}/toggle', [SMTPController::class, 'toggle'])->name('smtp.toggle');

    Route::get('/import', [ImportController::class, 'index'])->name('import.index');
    Route::post('/import', [ImportController::class, 'store'])->name('import.store');

    Route::get('/unsubscribes', [UnsubscribeController::class, 'index'])->name('unsubscribes.index');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
