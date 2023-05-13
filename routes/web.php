<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SchoolController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home',[ HomeController::class, 'index'])->name('home');

// Schools.
Route::get('schools', [SchoolController::class, 'index'])->name('schools');
Route::get('schools/create', [SchoolController::class, 'create'])->name('schools.create');
Route::get('schools/edit/{school}', [SchoolController::class, 'edit'])->name('schools.edit');
Route::get('schools/getSchools', [SchoolController::class, 'getSchools'])->name('schools.getSchools');
Route::get('schools/getMunicipalities', [SchoolController::class, 'getMunicipalities'])->name('schools.getMunicipalities');
Route::get('schools/getInstitutionTypes', [SchoolController::class, 'getInstitutionTypes'])->name('schools.getInstitutionTypes');
Route::get('schools/getSchool/{school}', [SchoolController::class, 'getSchool'])->name('schools.getSchool');
Route::post('schools/create', [SchoolController::class, 'store'])->name('schools.store');
Route::post('schools/edit', [SchoolController::class, 'update'])->name('schools.update');
