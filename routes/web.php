<?php

use App\Http\Controllers\AcountController;
use App\Http\Controllers\ArticleCategoryController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\MenuGroupController;
use App\Http\Controllers\OutletController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\DiningTableController;
use App\Http\Controllers\PermissionGroupController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\PromoController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductStockController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SocialiteController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CustomerPromoController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\KitchenController;
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

    Route::resource('/tables', DiningTableController::class, ['parameters' => [
        'tables' => 'table:uuid',
    ]])->except('show');

    Route::resource('/product_categories', ProductCategoryController::class, ['parameters' => [
        'product_categories' => 'productCategory:uuid',
    ]])->except('show');

    Route::resource('/products', ProductController::class, ['parameters' => [
        'products' => 'product:uuid',
    ]])->except('show');

    Route::resource('/product-stocks', ProductStockController::class, ['parameters' => [
        'product-stocks' => 'productStock:uuid',
    ]])->except('show');

    Route::resource('/stock-movements', StockMovementController::class, ['parameters' => [
        'stock-movements' => 'stockMovement:uuid',
    ]])->except('show');

    Route::resource('/customer-promos', CustomerPromoController::class, ['parameters' => [
        'customer-promos' => 'customerPromo:uuid',
    ]])->except('show');

    Route::resource('/orders', OrderController::class, ['parameters' => [
        'orders' => 'order:uuid',
    ]]);

    // POS Routes
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::get('/pos/products', [PosController::class, 'getProducts'])->name('pos.products');
    Route::post('/pos/favorite', [PosController::class, 'toggleFavorite'])->name('pos.favorite');
    Route::get('/pos/cart', [PosController::class, 'getCart'])->name('pos.cart');
    Route::post('/pos/cart/add', [PosController::class, 'addToCart'])->name('pos.cart.add');
    Route::post('/pos/cart/update', [PosController::class, 'updateCart'])->name('pos.cart.update');
    Route::delete('/pos/cart/remove', [PosController::class, 'removeFromCart'])->name('pos.cart.remove');
    Route::delete('/pos/cart/clear', [PosController::class, 'clearCart'])->name('pos.cart.clear');
    Route::post('/pos/promo/apply', [PosController::class, 'applyPromo'])->name('pos.promo.apply');
    Route::post('/pos/order', [PosController::class, 'processOrder'])->name('pos.order');
    Route::post('/pos/promo/remove', [PosController::class, 'removePromo'])->name('pos.promo.remove');

    Route::resource('/customers', CustomerController::class, ['parameters' => [
        'customers' => 'customer:uuid',
    ]])->except('show');

    // Kitchen Routes
    Route::get('/kitchen', [KitchenController::class, 'index'])->name('kitchen.index');
    Route::get('/kitchen/history', [KitchenController::class, 'history'])->name('kitchen.history');  // 👈 HARUS DI ATAS /{order}
    Route::get('/kitchen/new-orders', [KitchenController::class, 'getNewOrders'])->name('kitchen.new-orders');
    Route::post('/kitchen/item/{item}/status', [KitchenController::class, 'updateItemStatus'])->name('kitchen.item.status');
    Route::post('/kitchen/{order}/status', [KitchenController::class, 'updateStatus'])->name('kitchen.status');
    Route::get('/kitchen/{order}', [KitchenController::class, 'show'])->name('kitchen.show');
    Route::get('/kitchen/{order}/print', [KitchenController::class, 'print'])->name('kitchen.print');
    
    // Route::prefix('setting')->group(function () {
    //     Route::get('/',[App\Http\Controllers\SettingController::class, 'index'])->name('setting.index');
    //     Route::get('/create',[App\Http\Controllers\SettingController::class, 'create'])->name('setting.create');
    //     Route::post('/store',[App\Http\Controllers\SettingController::class, 'store'])->name('setting.store');
    //     // Route::get('/edit/{setting}',[App\Http\Controllers\SettingController::class, 'edit'])->name('setting.edit');
    //     // Route::put('/update/{setting}',[App\Http\Controllers\SettingController::class, 'update'])->name('setting.update');
    //     Route::delete('/delete/{setting}',[App\Http\Controllers\SettingController::class, 'delete'])->name('setting.delete');
    // });

});
