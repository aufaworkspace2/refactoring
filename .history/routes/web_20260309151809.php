<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WelcomeController;

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
