<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LevelController;

Route::get('/', [WelcomeController::class, 'index'])->name('welcome');

Route::prefix('welcome')->group(function () {

    Route::get('/search_alur', [WelcomeController::class,'search_alur']);
    Route::post('/cek_logout', [WelcomeController::class,'cek_logout']);
    Route::get('/menu/{id}', [WelcomeController::class,'menu']);
    Route::get('/help/{id}', [WelcomeController::class,'help']);
    Route::get('/cariuser/{id}', [WelcomeController::class,'cariuser']);
    Route::post('/change_language/{lang}/{i18}', [WelcomeController::class,'change_language']);
    Route::post('/fd', [WelcomeController::class,'fd']);

});

Route::prefix('level')->group(function () {
    Route::get('/', [LevelController::class, 'index'])->name('level.index');
    Route::get('index', [LevelController::class, 'index']);
    Route::post('search', [LevelController::class, 'search'])->name('level.search');
    Route::get('add', [LevelController::class, 'add'])->name('level.add');
    Route::get('view/{id}', [LevelController::class, 'view'])->name('level.view');
    Route::post('save/{save}', [LevelController::class, 'save'])->name('level.save');
    Route::post('delete', [LevelController::class, 'delete'])->name('level.delete');
    Route::get('pdf', [LevelController::class, 'pdf'])->name('level.pdf');
    Route::get('excel', [LevelController::class, 'excel'])->name('level.excel');

});

Route::get('/dashboard', [DashboardController::class, 'index']);
