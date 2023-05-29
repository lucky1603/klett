<?php

use App\Http\Controllers\AppUserController;
use App\Http\Controllers\CountryController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InstitutionTypeController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\MunicipalityController;
use App\Http\Controllers\ProfessionalStatusController;
use App\Http\Controllers\RemoteUserController;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\SubjectController;

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
Route::get('schools/export', [SchoolController::class, 'export'])->name('schools.export');
Route::get('schools/edit/{school}', [SchoolController::class, 'edit'])->name('schools.edit');
Route::get('schools/getSchools', [SchoolController::class, 'getSchools'])->name('schools.getSchools');
Route::get('schools/getMunicipalities', [SchoolController::class, 'getMunicipalities'])->name('schools.getMunicipalities');
Route::get('schools/getInstitutionTypes', [SchoolController::class, 'getInstitutionTypes'])->name('schools.getInstitutionTypes');
Route::get('schools/getSchool/{school}', [SchoolController::class, 'getSchool'])->name('schools.getSchool');
Route::post('schools/create', [SchoolController::class, 'store'])->name('schools.store');
Route::post('schools/edit', [SchoolController::class, 'update'])->name('schools.update');
Route::post('schools', [SchoolController::class, 'filterSchools'])->name('schools.filter');

Route::get('appusers', [AppUserController::class, 'index'])->name('appusers');
Route::get('appusers/data', [AppUserController::class, 'getAppUsers'])->name('appusers.data');
Route::get('appusers/export',[AppUserController::class, 'export'])->name('appusers.export');
Route::get('appusers/register', [AppUserController::class, 'register'])->name('appusers.register');
Route::post('appusers/create', [AppUserController::class, 'store'])->name('appusers.store');
Route::post('appusers/edit', [AppUserController::class, 'update'])->name('appusers.update');
Route::post('appusers/user', [AppUserController::class, 'getAppUser'])->name('appusers.user');

// Remote users (from KeyCloak).
Route::get('remoteusers', [RemoteUserController::class, 'index'])->name('remoteusers');
Route::get("remoteusers/keycloak", [RemoteUserController::class, 'connectKeyCloak'])->name("remoteusers.keycloak");
Route::post("remoteusers/data", [RemoteUserController::class, 'getData'])->name("remoteusers.data");

// Professional statuses.
Route::get('professional_statuses', [ProfessionalStatusController::class, 'data'])->name("professional_statuses");

// Countries.
Route::get("countries", [CountryController::class, 'data'])->name("countries");

// Subjects.
Route::get('subjects', [SubjectController::class, 'data'])->name('subjects');

// Municipalities.
Route::get('municipalities', [MunicipalityController::class, 'data'])->name('municipalities');

// Institution types.
Route::get('institution_types', [InstitutionTypeController::class, 'data'])->name('institution_types');

// Language-Localization.p
Route::get('/lang-{lang}.js', [LanguageController::class, 'show'])->name('languages.show');
