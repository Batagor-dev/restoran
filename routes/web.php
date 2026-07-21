<?php

use Illuminate\Support\Facades\Route;

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
})->name('home');

// Route untuk memicu login Google
Route::get('/auth/google', [App\Http\Controllers\SocialiteController::class, 'redirectToGoogle'])->name('google.login');

// Route callback tempat Google mengirim data kembali
Route::get('/auth/google/callback', [App\Http\Controllers\SocialiteController::class, 'handleGoogleCallback']);

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    
    Route::get('/user/role/{user}', [App\Http\Controllers\UserController::class, 'role'])->name('user.role');
    Route::post('/user/roleaction/{user}', [App\Http\Controllers\UserController::class, 'roleaction']);
    Route::resource('/user', App\Http\Controllers\UserController::class);

    Route::resource('/acount', App\Http\Controllers\AcountController::class)->only(['index', 'store']);
    Route::get('/acount/security', [App\Http\Controllers\AcountController::class, 'security'])->name('acount.security');
    Route::post('acount/password', [App\Http\Controllers\AcountController::class, 'updatePassword'])->name('acount.password');

    Route::post('/role/showaction/{role}', [App\Http\Controllers\RoleController::class, 'showaction']);
    Route::resource('/role', App\Http\Controllers\RoleController::class);


    Route::resource('/permissiongroup', App\Http\Controllers\PermissionGroupController::class)->except('show');

    Route::resource('/permission', App\Http\Controllers\PermissionController::class)->except('show');

    Route::resource('/menugroup', App\Http\Controllers\MenuGroupController::class)->except('show');
    Route::resource('/menu', App\Http\Controllers\MenuController::class)->except('show');
    Route::resource('/setting', App\Http\Controllers\SettingController::class)->only(['index', 'store']);

    Route::resource('/article_categories', App\Http\Controllers\ArticleCategoryController::class, ['parameters' => [
        'article_categories' => 'articleCategory:slug'
    ]])->except('show');

    Route::resource('/article', App\Http\Controllers\ArticleController::class)->parameters([
        'article' => 'article:slug',
    ]);

    // Route::prefix('setting')->group(function () {
    //     Route::get('/',[App\Http\Controllers\SettingController::class, 'index'])->name('setting.index');
    //     Route::get('/create',[App\Http\Controllers\SettingController::class, 'create'])->name('setting.create');
    //     Route::post('/store',[App\Http\Controllers\SettingController::class, 'store'])->name('setting.store');
    //     // Route::get('/edit/{setting}',[App\Http\Controllers\SettingController::class, 'edit'])->name('setting.edit');
    //     // Route::put('/update/{setting}',[App\Http\Controllers\SettingController::class, 'update'])->name('setting.update');
    //     Route::delete('/delete/{setting}',[App\Http\Controllers\SettingController::class, 'delete'])->name('setting.delete');
    // });
});
    