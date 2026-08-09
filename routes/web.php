<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CategorieController;
use App\Http\Controllers\EmplacementController;
use App\Http\Controllers\FactureController;
use App\Http\Controllers\FournisseurController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AccueilController;
use App\Http\Controllers\StatistiqueController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

// ----- Authentification -----
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// ----- Médias publics (hors auth pour les assets) -----
Route::get('/media/public/{path}', [MediaController::class, 'show'])
    ->where('path', '.*')
    ->name('media.public');

// ----- Routes protégées -----
Route::middleware(['auth'])->group(function () {

    // Tableau de bord
    Route::get('/', [AccueilController::class, 'index'])->name('accueil');

    // Statistiques
    Route::get('/statistiques', [StatistiqueController::class, 'index'])->name('statistiques.index');

    // Catalogue & Stock
    Route::resource('articles', ArticleController::class);
    Route::resource('categories', CategorieController::class);
    Route::resource('emplacements', EmplacementController::class);
    Route::resource('fournisseurs', FournisseurController::class);

    // Facturation
    Route::resource('factures', FactureController::class);
    Route::get('/factures/{facture}/pdf', [FactureController::class, 'genererPdf'])->name('factures.pdf');

    // Administration (réservée aux rôles disposant des permissions de gestion)
    Route::middleware('role:super_admin|manager')->group(function () {
        Route::resource('users', UserController::class);
        Route::resource('roles', RoleController::class);
        Route::resource('permissions', PermissionController::class);
    });

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{notification}', [NotificationController::class, 'show'])->name('notifications.show');
    Route::post('/notifications/{notification}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::post('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');
});
