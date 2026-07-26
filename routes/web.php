<?php

use App\Http\Controllers\AcountController;
use App\Http\Controllers\ArticleCategoryController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\MenuGroupController;
use App\Http\Controllers\OutletController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PermissionGroupController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\PromoController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SocialiteController;
use App\Http\Controllers\UserController;
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
Route::get('/auth/google', [SocialiteController::class, 'redirectToGoogle'])->name('google.login');

// Route callback tempat Google mengirim data kembali
Route::get('/auth/google/callback', [SocialiteController::class, 'handleGoogleCallback']);

Route::middleware(['auth', 'verified', 'set_default_outlet'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::post('/switch-outlet', [OutletController::class, 'switchOutlet'])->name('outlet.switch');
    Route::resource('/outlet', OutletController::class)->except('show');

    Route::get('/user/role/{user}', [UserController::class, 'role'])->name('user.role');
    Route::post('/user/roleaction/{user}', [UserController::class, 'roleaction']);
    Route::resource('/user', UserController::class);

    Route::resource('/acount', AcountController::class)->only(['index', 'store']);
    Route::get('/acount/security', [AcountController::class, 'security'])->name('acount.security');
    Route::post('acount/password', [AcountController::class, 'updatePassword'])->name('acount.password');

    Route::post('/role/showaction/{role}', [RoleController::class, 'showaction']);
    Route::resource('/role', RoleController::class);

    Route::resource('/permissiongroup', PermissionGroupController::class)->except('show');

    Route::resource('/permission', PermissionController::class)->except('show');

    Route::resource('/menugroup', MenuGroupController::class)->except('show');
    Route::resource('/menu', MenuController::class)->except('show');
    Route::resource('/setting', SettingController::class)->only(['index', 'store']);

    Route::resource('/article_categories', ArticleCategoryController::class, ['parameters' => [
        'article_categories' => 'articleCategory:slug',
    ]])->except('show');

    Route::resource('/article', ArticleController::class)->parameters([
        'article' => 'article:slug',
    ]);

    Route::resource('/product_categories', ProductCategoryController::class, ['parameters' => [
        'product_categories' => 'productCategory:uuid',
    ]])->except('show');

    Route::resource('/promo', PromoController::class, ['parameters' => [
        'promo' => 'promo:uuid',
    ]])->except('show');

    Route::resource('/product_categories', ProductCategoryController::class, ['parameters' => [
    'product_categories' => 'productCategory:uuid',
]])->except('show');

Route::resource('/products', ProductController::class, ['parameters' => [
    'products' => 'product:uuid',
]])->except('show');

    // Route::prefix('setting')->group(function () {
    //     Route::get('/',[App\Http\Controllers\SettingController::class, 'index'])->name('setting.index');
    //     Route::get('/create',[App\Http\Controllers\SettingController::class, 'create'])->name('setting.create');
    //     Route::post('/store',[App\Http\Controllers\SettingController::class, 'store'])->name('setting.store');
    //     // Route::get('/edit/{setting}',[App\Http\Controllers\SettingController::class, 'edit'])->name('setting.edit');
    //     // Route::put('/update/{setting}',[App\Http\Controllers\SettingController::class, 'update'])->name('setting.update');
    //     Route::delete('/delete/{setting}',[App\Http\Controllers\SettingController::class, 'delete'])->name('setting.delete');
    // });
});
