<?php

use App\Http\Controllers\ImageController;
use App\Http\Controllers\UserController;
use App\Livewire\LandingPage;
use Illuminate\Support\Facades\Route;


Route::get('user-morph-one-image', [UserController::class, 'morph_one'])->name('demo.user.index');
Route::get('images/{id}', [ImageController::class, 'getMedia']);
Route::get('/', function(){
    return 'hello koyeb';
});
