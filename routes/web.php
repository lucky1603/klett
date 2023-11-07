<?php

use App\Http\Controllers\AnonimousController;
use App\Http\Controllers\AppUserController;
use App\Http\Controllers\CRMController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\RemoteUserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserImportController;

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

Route::get('/anonimous/verify/{token}', [AnonimousController::class, 'verify'])->name('anonimous.verify');
Route::post('anonimous/password', [AnonimousController::class, 'password'])->name('anonimous.password');
Route::get('/refreshCaptcha', [AnonimousController::class, 'refreshCaptcha'])->name('anonimous.refreshCaptcha');
Route::get('/testRequestEdit', [AnonimousController::class, 'testRequestEdit'])->name('anonimous.testrequestedit');
Route::get('/requestEditProfile/{email}', [AnonimousController::class, 'requestEditProfile'])->name('anonimous.requesteditprofile');

Auth::routes();

Route::get('/home',[ HomeController::class, 'index'])->name('home');
Route::get('apply', [AppUserController::class, 'register'])->name('appusers.register');

// App users
Route::get('users', [UserController::class, 'index'])->name('users');
Route::post('users/filter', [UserController::class, 'filter'])->name('users.filter');
Route::post('users/create', [UserController::class, 'store'])->name('users.store');
Route::post('users/data', [UserController::class, 'getUserData'])->name('users.data');
Route::post('users/password', [UserController::class, 'updatePassword'])->name('users.updatePassword');
Route::post('users/delete', [UserController::class, 'delete'])->name('users.delete');

// Roles
Route::get('roles/list', [RoleController::class, 'list'])->name("roles.list");

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
Route::post('remoteusers/filterUsers1', [RemoteUserController::class, 'filterUsers1'])->name('remoteusers.filterUsers1');
Route::get('remoteusers/testmail/{email}', [RemoteUserController::class, 'testMail'])->name('remoteusers.testmail');
Route::post('remoteusers/adminupdate', [RemoteUserController::class, 'adminUpdate'])->name('remoteusers.adminupdate');
Route::post('remoteusers/adminstore', [RemoteUserController::class, 'adminStore'])->name('remoteusers.adminstore');
Route::post('remoteusers/count', [RemoteUserController::class, 'getCount'])->name('remoteusers.count');
Route::post('remoteusers/filtercount', [RemoteUserController::class, 'filterCount'])->name('remoteusers.filtercount');
Route::post('remoteusers/import', [RemoteUserController::class, 'importUser'])->name('remoteusers.import');
Route::get('remoteusers/importFirstUser', [RemoteUserController::class, 'importFirstUser'])->name('remoteusers.importFirstUser');
Route::get('remoteusers/deleteall', [RemoteUserController::class, 'deleteAll'])->name('remoteusers.deleteAll');
Route::get('remoteusers/importall', [RemoteUserController::class, 'importAllUsers'])->name('remoteusers.importAll');
Route::get('remoteusers/scheduledEdit/{token}', [RemoteUserController::class, 'editScheduled'])->name('remoteusers.scheduledEdit');
Route::get('remoteusers/unimporteduserids', [RemoteUserController::class, 'getUnimportedUserIds'])->name('remoteusers.unimporteduserids');
Route::get('remoteusers/importuserbyid/{user}', [RemoteUserController::class, 'importUserById'])->name('remoteusers.importeduserbyid');
// CRM
Route::post('crm/token', [CRMController::class, 'connectCRM'])->name('crm.token');
Route::get('crm/checkUser/{email}', [CRMController::class, 'checkUser'])->name('crm.checkUser');
Route::get('crm/opstine', [CRMController::class, 'getOpstine'])->name('crm.opstine');
Route::get('crm/tipoviKontakata', [CRMController::class, 'getTipoviKontakata'])->name('crm.tipoviKontakata');
Route::post('crm/skole', [CRMController::class, 'getSkole'])->name('crm.skole');
Route::get('crm/predmeti', [CRMController::class, 'getPredmeti'])->name('crm.predmeti');
Route::get('crm/arrayPredmeta', [CRMController::class, 'arrayPredmeta'])->name('crm.arrayPredmeta');

// User imports.
Route::get('userimports', [UserImportController::class, 'index'])->name('userimports.index');
Route::get('userimports/data', [UserImportController::class, 'data'])->name('userimports.data');
Route::get('userimports/updateImported/{user}', [UserImportController::class, 'updateImported'])->name('userimports.updateImported');
Route::get('userimports/counts', [UserImportController::class, 'counts'])->name('userimports.count');
Route::get('userimports/reset', [UserImportController::class, 'reset'])->name('userimports.reset');
Route::get('userimports/files', [UserImportController::class, 'files'])->name('userimports.files');
Route::post('userimports/setimport', [UserImportController::class, 'setImport'])->name('userimports.setimport');

// Language-Localization.p
Route::get('/lang-{lang}.js', [LanguageController::class, 'show'])->name('languages.show');
