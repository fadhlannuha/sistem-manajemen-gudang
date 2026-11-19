<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard.index');
    }

    return redirect()->route('login');
})->name('home');
