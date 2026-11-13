<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GreenFundController;
use App\Http\Controllers\GreenFundTaskCompletionController;
use App\Http\Controllers\GreenFundWorkflowController;
use App\Http\Middleware\EnsureGreenFundRole;
use App\Http\Controllers\Teams\TeamInvitationController;
use App\Http\Middleware\EnsureTeamMembership;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/greenfund/dashboard')->name('home');

Route::get('/greenfund/{page?}', GreenFundController::class)
    ->where('page', 'dashboard|map|reports|submit|tasks|inventory|maintenance|analytics|settings')
    ->name('greenfund.screen');

Route::post('/greenfund/tasks/{task}/complete', GreenFundTaskCompletionController::class)
    ->middleware(['auth', EnsureGreenFundRole::class.':operator,executor'])
    ->name('greenfund.tasks.complete');
Route::patch('/greenfund/tasks/{task}/progress', [GreenFundTaskCompletionController::class, 'updateProgress'])
    ->middleware(['auth', EnsureGreenFundRole::class.':operator,executor'])
    ->name('greenfund.tasks.progress');

Route::middleware(['auth', EnsureGreenFundRole::class.':operator'])->group(function () {
    Route::patch('/greenfund/reports/{report}', [GreenFundWorkflowController::class, 'updateReport'])
        ->name('greenfund.reports.update');
    Route::post('/greenfund/tasks', [GreenFundWorkflowController::class, 'storeTask'])
        ->name('greenfund.tasks.store');
});

Route::middleware(['auth', EnsureGreenFundRole::class.':operator,landscaping_specialist'])->group(function () {
    Route::post('/greenfund/assets', [GreenFundWorkflowController::class, 'storeAsset'])
        ->name('greenfund.assets.store');
    Route::patch('/greenfund/assets/{greenAsset}', [GreenFundWorkflowController::class, 'updateAsset'])
        ->name('greenfund.assets.update');
    Route::post('/greenfund/assets/{greenAsset}/photos', [GreenFundWorkflowController::class, 'storeAssetPhotos'])
        ->name('greenfund.assets.photos.store');
    Route::post('/greenfund/maintenance', [GreenFundWorkflowController::class, 'storeMaintenance'])
        ->name('greenfund.maintenance.store');
    Route::patch('/greenfund/maintenance/{maintenanceRecord}/complete', [GreenFundWorkflowController::class, 'completeMaintenance'])
        ->name('greenfund.maintenance.complete');
});

Route::prefix('{current_team}')
    ->middleware(['auth', 'verified', EnsureTeamMembership::class])
    ->group(function () {
        Route::get('dashboard', DashboardController::class)->name('dashboard');
    });

Route::middleware(['auth'])->group(function () {
    Route::post('invitations/{invitation}/accept', [TeamInvitationController::class, 'accept'])->name('invitations.accept');
    Route::delete('invitations/{invitation}', [TeamInvitationController::class, 'decline'])->name('invitations.decline');
});

require __DIR__.'/settings.php';
