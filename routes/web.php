<?php

use App\Http\Controllers\CampaignController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DripCampaignController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\BounceController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ContactTagController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\SMTPController;
use App\Http\Controllers\SendController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SingleEmailController;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\SuppressionController;
use App\Http\Controllers\UnsubscribeController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// SES webhook — no auth required
Route::post('/webhooks/ses-bounce', [BounceController::class, 'sesBounce'])->name('webhooks.ses-bounce');

Route::get('/track/open/{id}', [TrackingController::class, 'open'])->name('track.open');
Route::get('/track/click/{id}', [TrackingController::class, 'click'])->name('track.click');
Route::get('/unsubscribe/{email}', [UnsubscribeController::class, 'unsubscribe'])->name('unsubscribe');

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/contacts/bulk-delete', function () {
        return redirect()->route('contacts.index')
            ->withErrors(['ids' => 'Please select contact(s) from the Contacts page and use Delete Selected.']);
    })->name('contacts.bulk-delete.preview');
    Route::post('/contacts/bulk-delete', [ContactController::class, 'bulkDelete'])->name('contacts.bulk-delete');
    Route::post('/contacts/bulk-assign-group', [ContactController::class, 'bulkAssignGroup'])->name('contacts.bulk-assign-group');
    Route::get('/contacts/export', [ContactController::class, 'export'])->name('contacts.export');
    Route::resource('contacts', ContactController::class)->except(['show']);
    Route::post('/contacts/{contact}/tags', [ContactTagController::class, 'store'])->name('contacts.tags.store');
    Route::delete('/contacts/{contact}/tags/{tag}', [ContactTagController::class, 'destroy'])->name('contacts.tags.destroy');
    Route::resource('groups', GroupController::class)->only(['index', 'store', 'destroy']);
    Route::resource('campaigns', CampaignController::class)->except(['show']);
    Route::get('/campaigns/{campaign}/live-stats', [CampaignController::class, 'liveStats'])->name('campaigns.live-stats');
    Route::post('/campaigns/{campaign}/send', [SendController::class, 'sendNow'])->middleware('throttle:10,1')->name('campaigns.send');
    Route::post('/campaigns/{campaign}/pause', [SendController::class, 'pause'])->middleware('throttle:30,1')->name('campaigns.pause');
    Route::post('/campaigns/{campaign}/resume', [SendController::class, 'resume'])->middleware('throttle:30,1')->name('campaigns.resume');
    Route::post('/campaigns/{campaign}/send-test-email', [CampaignController::class, 'sendTestEmail'])->name('campaigns.send-test-email');
    Route::post('/campaigns/{campaign}/duplicate', [CampaignController::class, 'duplicate'])->name('campaigns.duplicate');

    Route::get('/smtp', [SMTPController::class, 'index'])->name('smtp.index');
    Route::get('/smtp-health', [SMTPController::class, 'health'])->name('smtp.health');
    Route::post('/smtp', [SMTPController::class, 'store'])->name('smtp.store');
    Route::get('/smtp/{smtp}/edit', [SMTPController::class, 'edit'])->name('smtp.edit');
    Route::put('/smtp/{smtp}', [SMTPController::class, 'update'])->name('smtp.update');
    Route::delete('/smtp/{smtp}', [SMTPController::class, 'destroy'])->name('smtp.destroy');
    Route::patch('/smtp/{smtp}/toggle', [SMTPController::class, 'toggle'])->name('smtp.toggle');
    Route::post('/smtp/{smtp}/test', [SMTPController::class, 'testConnection'])->middleware('throttle:10,1')->name('smtp.test');
    Route::post('/smtp/{smtp}/send-test-email', [SMTPController::class, 'sendTestEmail'])->middleware('throttle:10,1')->name('smtp.send-test-email');
    Route::post('/smtp/bulk-upload', [SMTPController::class, 'bulkUpload'])->name('smtp.bulk-upload');

    Route::get('/import', [ImportController::class, 'index'])->name('import.index');
    Route::post('/import', [ImportController::class, 'store'])->name('import.store');

    Route::get('/unsubscribes', [UnsubscribeController::class, 'index'])->name('unsubscribes.index');
    Route::delete('/unsubscribes/{unsubscribe}', [UnsubscribeController::class, 'destroy'])->name('unsubscribes.destroy');

    // Suppression List
    Route::get('/suppression', [SuppressionController::class, 'index'])->name('suppression.index');
    Route::post('/suppression', [SuppressionController::class, 'store'])->name('suppression.store');
    Route::delete('/suppression/{suppression}', [SuppressionController::class, 'destroy'])->name('suppression.destroy');
    Route::post('/suppression/bulk-import', [SuppressionController::class, 'bulkImport'])->name('suppression.bulk-import');

    // Bounces
    Route::get('/bounces', [BounceController::class, 'index'])->name('bounces.index');
    Route::post('/contacts/{contact}/mark-bounced', [BounceController::class, 'markBounced'])->name('contacts.mark-bounced');
    Route::post('/contacts/{contact}/clear-bounced', [BounceController::class, 'clearBounced'])->name('contacts.clear-bounced');
    Route::post('/unsubscribes/{unsubscribe}/delete', [UnsubscribeController::class, 'destroy'])->name('unsubscribes.delete');
    Route::get('/single-email', [SingleEmailController::class, 'create'])->name('single-email.create');
    Route::post('/single-email', [SingleEmailController::class, 'store'])->middleware('throttle:20,1')->name('single-email.store');
    Route::get('/reports', [ReportsController::class, 'index'])->name('reports.index');
    Route::get('/reports/single-email', [ReportsController::class, 'singleEmailReport'])->name('reports.single-email');
    Route::get('/reports/email/{id}', [ReportsController::class, 'showEmail'])->name('reports.email.show');
    Route::get('/reports/campaign/{campaign_id}', [ReportsController::class, 'campaignDetail'])->name('reports.campaign.detail');

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::put('/settings/webhook', [SettingsController::class, 'updateWebhook'])->name('settings.webhook');
    Route::put('/settings/branding', [SettingsController::class, 'updateBranding'])->name('settings.branding');

    // Drip Campaigns
    Route::get('/drip', [DripCampaignController::class, 'index'])->name('drip.index');
    Route::get('/drip/create', [DripCampaignController::class, 'create'])->name('drip.create');
    Route::post('/drip', [DripCampaignController::class, 'store'])->name('drip.store');
    Route::get('/drip/{drip}', [DripCampaignController::class, 'show'])->name('drip.show');
    Route::get('/drip/{drip}/edit', [DripCampaignController::class, 'edit'])->name('drip.edit');
    Route::put('/drip/{drip}', [DripCampaignController::class, 'update'])->name('drip.update');
    Route::delete('/drip/{drip}', [DripCampaignController::class, 'destroy'])->name('drip.destroy');
    Route::post('/drip/{drip}/activate', [DripCampaignController::class, 'activate'])->name('drip.activate');
    Route::post('/drip/{drip}/pause', [DripCampaignController::class, 'pause'])->name('drip.pause');
    Route::post('/drip/{drip}/enroll', [DripCampaignController::class, 'enroll'])->name('drip.enroll');
    Route::delete('/drip/{drip}/enrollments/{enrollment}', [DripCampaignController::class, 'unenroll'])->name('drip.unenroll');
    // Drip Steps
    Route::post('/drip/{drip}/steps', [DripCampaignController::class, 'storeStep'])->name('drip.steps.store');
    Route::put('/drip/{drip}/steps/{step}', [DripCampaignController::class, 'updateStep'])->name('drip.steps.update');
    Route::delete('/drip/{drip}/steps/{step}', [DripCampaignController::class, 'destroyStep'])->name('drip.steps.destroy');

    Route::resource('templates', EmailTemplateController::class)->except(['show']);
    Route::get('/templates/{template}/load', [EmailTemplateController::class, 'show'])->name('templates.load');

    Route::middleware('admin')->group(function () {
        Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserManagementController::class, 'create'])->name('users.create');
        Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserManagementController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');
        Route::post('/users/bulk-delete', [UserManagementController::class, 'bulkDelete'])->name('users.bulk-delete');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
