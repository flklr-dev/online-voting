<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\ElectionController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\CandidateController;
use App\Http\Controllers\AdminController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
Route::post('/logout', function () {
    // Placeholder action for logout
    // You can redirect to the home page or any view you like
    return redirect('/')->with('message', 'Logged out successfully.');
})->name('logout');

//Admin Dashboard
Route::resource('students', StudentController::class);
Route::resource('elections', ElectionController::class);
Route::resource('positions', PositionController::class);
Route::get('/candidates/search-students', [CandidateController::class, 'searchStudents'])->name('candidates.searchStudents');
Route::resource('candidates', CandidateController::class);
Route::resource('admins', AdminController::class);
Route::get('/results', function () {
    return view('results.index'); // Create a results/index.blade.php
})->name('results.index');