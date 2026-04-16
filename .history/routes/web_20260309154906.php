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
    Route::get('/', [LevelController::class, 'index'])->name('LevelController.index');
    Route::get('index', [LevelController::class, 'index']);
    Route::post('search', [LevelController::class, 'search'])->name('LevelController.search');
    Route::get('add', [LevelController::class, 'add'])->name('LevelController.add');
    Route::get('view/{id}', [LevelController::class, 'view'])->name('LevelController.view');
    Route::post('save/{save}', [LevelController::class, 'save'])->name('LevelController.save');
    Route::post('delete', [LevelController::class, 'delete'])->name('LevelController.delete');
    Route::get('pdf', [LevelController::class, 'pdf'])->name('LevelController.pdf');
    Route::get('excel', [LevelController::class, 'excel'])->name('LevelController.excel');

});

Route::get('/dashboard', [DashboardController::class, 'index']);
