<?php

use App\Http\Controllers\AppUserController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\CRMController;
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


Route::get('register', [AppUserController::class, 'register'])->name('appusers.register');

// Remote users (from KeyCloak).
Route::get('remoteusers', [RemoteUserController::class, 'index'])->name('remoteusers');
Route::get("remoteusers/keycloak", [RemoteUserController::class, 'connectKeyCloak'])->name("remoteusers.keycloak");
Route::get("remoteusers/create", [RemoteUserController::class, 'create'])->name('remoteusers.create');
Route::post("remoteusers/userData", [RemoteUserController::class, 'userData'])->name('remoteusers.userData');
Route::post('remoteusers/update', [RemoteUserController::class, 'update'])->name('remoteusers.update');
Route::post('remoteusers/create', [RemoteUserController::class, 'store'])->name('remoteusers.store');
Route::post("remoteusers/data", [RemoteUserController::class, 'getData'])->name("remoteusers.data");
Route::get("remoteusers/{user}/updatePassword", [RemoteUserController::class, 'sendUpdatePasswordNotice']);
Route::post("remoteusers/delete", [RemoteUserController::class, 'delete'])->name('remoteusers.delete');
Route::get("remoteusers/export", [RemoteUserController::class, 'export'])->name('remoteusers.export');
Route::get("remoteusers/getRealmGroups", [RemoteUserController::class, 'getRealmGroups'])->name('remoteusers.getRealmGroups');
Route::post("remoteusers/user_groups", [RemoteUserController::class, 'userGroups'])->name('remoteusers.userGroups');
Route::post("remoteusers/user_group", [RemoteUserController::class, 'userGroup'])->name('remoteusers.userGroup');
Route::post("remoteusers/setUserGroup", [RemoteUserController::class, 'setUserGroup'])->name('remoteusers.setUserGroup');
Route::get("remoteusers/getGroupIdByName/{user}/{group}", [RemoteUserController::class, 'getGroupIdByName'])->name('remoteusers.getGroupIdByName');
Route::post('remoteusers/filterUsers', [RemoteUserController::class, 'filterUsers'])->name('remoteusers.filterUsers');
Route::get('remoteusers/testmail/{email}', [RemoteUserController::class, 'testMail'])->name('remoteusers.testmail');

// CRM
Route::post('crm/token', [CRMController::class, 'connectCRM'])->name('crm.token');
Route::get('crm/checkUser/{email}', [CRMController::class, 'checkUser'])->name('crm.checkUser');
Route::get('crm/opstine', [CRMController::class, 'getOpstine'])->name('crm.opstine');
Route::get('crm/tipoviKontakata', [CRMController::class, 'getTipoviKontakata'])->name('crm.tipoviKontakata');
Route::post('crm/skole', [CRMController::class, 'getSkole'])->name('crm.skole');
Route::get('crm/predmeti', [CRMController::class, 'getPredmeti'])->name('crm.predmeti');

// Language-Localization.p
Route::get('/lang-{lang}.js', [LanguageController::class, 'show'])->name('languages.show');
