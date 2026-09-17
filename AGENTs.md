# AGENTS.md

## 📋 Project Overview

**Project Name:** POS CMS Multi Outlet

**Stack:**
| Komponen | Teknologi |
|---|---|
| Framework | Laravel 11 |
| Database | PostgreSQL |
| Styling | TailwindCSS |
| Interaktivitas | AlpineJS |
| Authorization | Spatie Permission |
| Authentication | Laravel Fortify |
| OAuth | Laravel Socialite |
| Tabel Server-side | Yajra Laravel DataTables |
| Breadcrumbs | Diglactic Laravel Breadcrumbs |
| Slug | Spatie Laravel Sluggable |
| Image Processing | Intervention Image v3 |
| Linter | Laravel Pint |

**Architecture:** Modular Monolith

---

## 🎯 Goal

Membangun sistem **POS + CMS Multi Outlet** untuk bisnis F&B, yang scalable untuk:

- Single Outlet
- Multi Outlet
- Franchise
- Enterprise

> ⚠️ Semua fitur baru **wajib** mempertimbangkan konsep Multi Outlet.

---

## 🏢 Multi Outlet Concept

Project menggunakan **Single Database Multi Tenant** — bukan satu database per outlet.

Semua data operasional dipisahkan menggunakan kolom `outlet_id`.

> ⚠️ Setiap query wajib menggunakan `outlet_id`, kecuali data global.

---

## 🗂️ Hierarchy

```
Company
│
└── Outlet
    ├── Employee
    ├── Stock
    ├── Transaction
    ├── Expense
    ├── Cashier
    └── Printer
```

---

## 🔐 Authentication

- Login menggunakan **email**.
- Setelah login, user memiliki:
  - Role
  - Permission
  - Accessible Outlets
- Satu user bisa punya lebih dari satu outlet.

**Contoh:**
```
User A
├── Outlet Bandung
└── Outlet Jakarta
```

> Super Admin memiliki akses ke seluruh outlet.

---

## 🛡️ Authorization

Menggunakan **Role Based Access Control (RBAC)**.

**Contoh Role:**
- Super Admin
- Owner
- Manager
- Cashier
- Kitchen
- Waiter

| Konsep | Fungsi |
|---|---|
| **Permission** | Mengatur *apa* yang boleh dilakukan (contoh: `create transaction`, `update product`, `delete outlet`, `view report`) |
| **Outlet** | Menentukan *data mana* yang boleh diakses |

---

## 📊 Data Scope

Semua query harus mengikuti outlet aktif.

```php
Transaction::where('outlet_id', auth()->user()->current_outlet_id);
```

> ⚠️ Jangan pernah mengambil seluruh data tanpa filter outlet, kecuali untuk Super Admin.

---

## 🌐 Global Tables

Tidak memiliki `outlet_id`:

- `companies`
- `roles`
- `permissions`
- `users`
- `categories`
- `products`
- `taxes`
- `units`

## 🏬 Outlet Tables

Wajib memiliki `outlet_id`:

- `stocks`
- `transactions`
- `transaction_items`
- `expenses`
- `shifts`
- `printers`
- `employees`
- `cash_drawers`

---

## 📦 Product Strategy

Product bersifat **global**. Harga dan stok bisa berbeda per outlet.

Gunakan tabel `product_outlets`:

```
id
product_id
outlet_id
price
stock
minimum_stock
status
```

> ⚠️ Jangan menyimpan `stock` langsung di tabel `products`.

---

## 👥 User Outlet Relation

Gunakan relasi **many-to-many**.

```
users
├── id
├── name
└── email
```

```
outlets
├── id
├── company_id
└── name
```

```
outlet_user (pivot)
├── id
├── user_id
├── outlet_id
└── role_id
```

---

## 📐 Coding Rules

- Gunakan **Service Layer**.
- Jangan letakkan business logic di Controller.
- Gunakan **Repository** bila query kompleks.
- Validasi menggunakan **Form Request**.
- Gunakan **Policy** untuk Authorization.
- Hindari Query Builder di Blade.
- Hindari N+1 Query.

---

## 🧩 Developer Guide & Client Code Patterns

### 1. Models & UUID

Setiap model utama wajib memiliki kolom `id` (primary key auto-increment internal) dan `uuid` (untuk public exposure / URL).

- Gunakan trait `App\Models\Traits\HasUuid` untuk generate UUID otomatis saat record dibuat.
- Override `getRouteKeyName` untuk binding route menggunakan `uuid`.
- Gunakan `SoftDeletes` jika model memerlukan pengamanan data dari penghapusan permanen.

```php
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\SoftDeletes;

class Menu extends Model
{
    use HasUuid, SoftDeletes;

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
```

### 2. Yajra DataTables (Server-side Table)

Untuk list data kompleks, gunakan Yajra DataTables dengan layout custom Tailwind + Remix Icons.

- Definisikan class DataTable di `app/DataTables/` (contoh: `MenuDataTable.php`).
- Atur styling tabel di method `html()` menggunakan class Tailwind agar seragam dengan UI yang ada.
- Render class DataTable dari controller:

```php
public function index(MenuDataTable $dataTable)
{
    return $dataTable->render('menu.index');
}
```

### 3. Controller & Request Validation

- Controller harus bersih dari logic bisnis yang berat.
- Gunakan Form Request khusus untuk validasi input (contoh: `StoreMenuRequest`).
- Kirim data ke view menggunakan structured array `$this->data`.

### 4. Settings System Helper

Untuk mengakses/menyimpan konfigurasi aplikasi global, gunakan helper `settings()` atau static method pada model `Setting`:

| Method | Fungsi |
|---|---|
| `Setting::getValue('key', $default)` | Ambil value (auto-deserialize jika array/JSON) |
| `Setting::setValue(['key1' => 'val1', 'key2' => 'val2'])` | Upsert + auto-serialize |
| `Setting::deleteOldFile('key')` | Bersihkan disk dari file lama saat di-overwrite |

### 5. Breadcrumbs

Aplikasi menggunakan `diglactic/laravel-breadcrumbs`. Setiap halaman/route baru **wajib** didaftarkan breadcrumb-nya di `routes/breadcrumbs.php` agar navigasi user mudah.

### 6. Image Resizing & Compression

Gunakan `App\Services\ImageService` (memanfaatkan `Intervention Image v3`) untuk kompresi dan scale aspect-ratio otomatis — menghemat storage dan mengoptimalkan load time.


### 7. Structure folder

Directory structure:
└── app/
    ├── Actions/
    │   └── Fortify/
    │       ├── CreateNewUser.php
    │       ├── LoginRedirect.php
    │       ├── PasswordValidationRules.php
    │       ├── ResetUserPassword.php
    │       ├── UpdateUserPassword.php
    │       └── UpdateUserProfileInformation.php
    ├── DataTables/
    │   ├── ArticleCategoryDataTable.php
    │   ├── ArticleDataTable.php
    │   ├── CustomerDataTable.php
    │   ├── CustomerPromoDataTable.php
    │   ├── DiningTableDataTable.php
    │   ├── MenuDataTable.php
    │   ├── MenuGroupDataTable.php
    │   ├── OrderDataTable.php
    │   ├── OutletDataTable.php
    │   ├── PermissionDataTable.php
    │   ├── PermissionGroupDataTable.php
    │   ├── ProductCategoryDataTable.php
    │   ├── ProductDataTable.php
    │   ├── ProductStockDataTable.php
    │   ├── PromoDataTable.php
    │   ├── RoleDataTable.php
    │   ├── StockMovementDataTable.php
    │   ├── TransactionDataTable.php
    │   └── UserDataTable.php
    ├── Helpers/
    │   ├── helpers.php
    │   └── MenuHelper.php
    ├── Http/
    │   ├── Controllers/
    │   │   ├── AcountController.php
    │   │   ├── AdminPanelController.php
    │   │   ├── ArticleCategoryController.php
    │   │   ├── ArticleController.php
    │   │   ├── Controller.php
    │   │   ├── CustomerController.php
    │   │   ├── CustomerPromoController.php
    │   │   ├── DashboardController.php
    │   │   ├── DiningTableController.php
    │   │   ├── KitchenController.php
    │   │   ├── MenuController.php
    │   │   ├── MenuGroupController.php
    │   │   ├── OrderController.php
    │   │   ├── OutletController.php
    │   │   ├── PermissionController.php
    │   │   ├── PermissionGroupController.php
    │   │   ├── PosController.php
    │   │   ├── ProductCategoryController.php
    │   │   ├── ProductController.php
    │   │   ├── ProductStockController.php
    │   │   ├── PromoController.php
    │   │   ├── ReportController.php
    │   │   ├── RoleController.php
    │   │   ├── SettingController.php
    │   │   ├── SocialiteController.php
    │   │   ├── StockMovementController.php
    │   │   ├── TransactionController.php
    │   │   └── UserController.php
    │   ├── Middleware/
    │   │   └── SetDefaultOutletMiddleware.php
    │   └── Requests/
    │       ├── RefundTransactionRequest.php
    │       ├── StoreArticleCategoryRequest.php
    │       ├── StoreArticleRequest.php
    │       ├── StoreCustomerPromoRequest.php
    │       ├── StoreCustomerRequest.php
    │       ├── StoreDiningTableRequest.php
    │       ├── StoreMenuGroupRequest.php
    │       ├── StoreMenuRequest.php
    │       ├── StoreOrderRequest.php
    │       ├── StoreOutletRequest.php
    │       ├── StorePermissionGroupRequest.php
    │       ├── StorePermissionRequest.php
    │       ├── StoreProductCategoryRequest.php
    │       ├── StoreProductRequest.php
    │       ├── StoreProductStockRequest.php
    │       ├── StorePromoRequest.php
    │       ├── StoreRoleRequest.php
    │       ├── StoreSettingRequest.php
    │       ├── StoreStockMovementRequest.php
    │       ├── StoreUserRequest.php
    │       ├── UpdateAcountRequest.php
    │       ├── UpdateArticleCategoryRequest.php
    │       ├── UpdateArticleRequest.php
    │       ├── UpdateCustomerPromoRequest.php
    │       ├── UpdateCustomerRequest.php
    │       ├── UpdateDiningTableRequest.php
    │       ├── UpdateMenuGroupRequest.php
    │       ├── UpdateMenuRequest.php
    │       ├── UpdateOrderRequest.php
    │       ├── UpdateOutletRequest.php
    │       ├── UpdatePasswordRequest.php
    │       ├── UpdatePermissionGroupRequest.php
    │       ├── UpdatePermissionRequest.php
    │       ├── UpdateProductCategoryRequest.php
    │       ├── UpdateProductRequest.php
    │       ├── UpdateProductStockRequest.php
    │       ├── UpdatePromoRequest.php
    │       ├── UpdateRoleRequest.php
    │       ├── UpdateUserRequest.php
    │       └── VoidTransactionRequest.php
    ├── Models/
    │   ├── Article.php
    │   ├── ArticleCategory.php
    │   ├── Customer.php
    │   ├── CustomerPromo.php
    │   ├── DiningTable.php
    │   ├── FavoriteProduct.php
    │   ├── Menu.php
    │   ├── MenuGroup.php
    │   ├── Order.php
    │   ├── OrderItem.php
    │   ├── Outlet.php
    │   ├── Permission.php
    │   ├── PermissionGroup.php
    │   ├── Product.php
    │   ├── ProductCategory.php
    │   ├── ProductStock.php
    │   ├── Promo.php
    │   ├── Role.php
    │   ├── Setting.php
    │   ├── StockMovement.php
    │   ├── Traits/
    │   │   ├── BelongsToOutlet.php
    │   │   ├── Blameable.php
    │   │   ├── FindByKey.php
    │   │   ├── HasSlug.php
    │   │   ├── HasUuid.php
    │   │   └── VisibleToCurrentOutlet.php
    │   └── User.php
    ├── Policies/
    ├── Providers/
    │   ├── AppServiceProvider.php
    │   ├── AuthServiceProvider.php
    │   ├── FortifyServiceProvider.php
    │   └── SidebarServiceProvider.php
    ├── Rules/
    │   └── CheckingLengthDescription.php
    ├── services/
    │   ├── ImageService.php
    │   └── TransactionService.php
    └── View/
        └── Components/
            ├── forms/
            │   └── horizontal/
            │       ├── file.php
            │       ├── input.php
            │       ├── layout.php
            │       ├── select2.php
            │       ├── switches.php
            │       └── textarea.php
            ├── Layout/
            │   └── Admin/
            │       └── Breadcrumb.php
            └── ui/
                ├── badge.php
                ├── button.php
                ├── card.php
                ├── checkbox.php
                ├── input.php
                └── password.php
Directory structure:
└── database/
    ├── factories/
    │   └── UserFactory.php
    ├── migrations/
    │   ├── 0001_01_01_000000_create_users_table.php
    │   ├── 0001_01_01_000001_create_cache_table.php
    │   ├── 0001_01_01_000002_create_jobs_table.php
    │   ├── 2025_08_08_073155_add_two_factor_columns_to_users_table.php
    │   ├── 2025_08_08_073900_create_menu_groups_table.php
    │   ├── 2025_08_08_074000_create_menus_table.php
    │   ├── 2025_08_08_075000_create_permission_groups_table.php
    │   ├── 2025_08_08_075917_create_permission_tables.php
    │   ├── 2025_09_21_141000_create_outlets_table.php
    │   ├── 2025_09_21_142000_create_outlet_user_table.php
    │   ├── 2025_09_22_040912_create_settings_table.php
    │   ├── 2025_09_22_134543_create_article_categories_table.php
    │   ├── 2025_09_22_140915_create_articles_table.php
    │   ├── 2025_09_22_143000_add_current_outlet_id_to_users_table.php
    │   ├── 2026_07_01_130238_create_customers_table.php
    │   ├── 2026_07_25_210000_create_promos_table.php
    │   ├── 2026_07_25_220000_create_dining_tables_table.php
    │   ├── 2026_07_26_023313_create_product_categories_table.php
    │   ├── 2026_07_26_023335_create_products_table.php
    │   ├── 2026_07_30_103420_create_product_stocks_table.php
    │   ├── 2026_07_30_103440_create_stock_movements_table.php
    │   ├── 2026_07_30_105457_add_is_active_to_outlets_table.php
    │   ├── 2026_08_01_093236_create_customer_promos_table.php
    │   ├── 2026_08_01_115059_create_orders_table.php
    │   ├── 2026_08_01_115129_create_order_items_table.php
    │   ├── 2026_08_01_124142_create_favorite_products_table.php
    │   ├── 2026_08_12_045217_add_kitchen_status_to_order_items_table.php
    │   ├── 2026_08_26_000001_create_promo_products_and_promo_categories_tables.php
    │   ├── 2026_08_26_000002_add_price_to_product_stocks_table.php
    │   ├── 2026_08_26_000003_add_promo_id_to_orders_table.php
    │   ├── 2026_08_26_000004_add_order_type_and_outlet_scoping.php
    │   ├── 2026_08_26_000005_add_transaction_fields_to_orders_table.php
    │   ├── 2026_08_26_000006_extend_permission_groups_name.php
    │   └── 2026_08_26_000007_add_cost_price_to_products_table.php
    └── seeders/
        ├── DatabaseSeeder.php
        ├── MenuGroupSeeder.php
        ├── MenuSeeder.php
        └── RolePermissionSeeder.php
Directory structure:
└── public/
    ├── assets/
    │   ├── font/
    │   │   └── satoshi/
    │   │       ├── Satoshi-Variable.ttf
    │   │       └── Satoshi-VariableItalic.ttf
    │   ├── img/
    │   │   ├── avatar/
    │   │   │   ├── avatar-1.jpg
    │   │   │   ├── avatar-2.jpg
    │   │   │   ├── avatar-3.jpg
    │   │   │   └── avatar-4.jpg
    │   │   ├── ilustrasi/
    │   │   │   └── Marketing-cuate.svg
    │   │   └── logo/
    │   │       └── logo.png
    │   ├── js/
    │   │   └── datatable/
    │   │       └── data-table.js
    │   └── vendor/
    │       └── datatables/
    │           ├── css/
    │           │   └── datatables.min.css
    │           └── js/
    │               └── datatables.min.js
    ├── build/
    │   ├── assets/
    │   │   ├── app-CJKR4bbR.css
    │   │   └── app-CKt82LXN.js
    │   └── manifest.json
    ├── favicon.ico
    ├── hot
    ├── index.php
    ├── robots.txt
    └── storage/
Directory structure:
└── resources/
    ├── css/
    │   ├── app.css
    │   ├── datatable.css
    │   └── select2-tailwind.css
    ├── js/
    │   └── app.js
    └── views/
        ├── acount/
        │   ├── index.blade.php
        │   └── security.blade.php
        ├── adminpanel/
        │   └── index.blade.php
        ├── article/
        │   ├── detail.blade.php
        │   ├── form.blade.php
        │   └── index.blade.php
        ├── article_categories/
        │   ├── form.blade.php
        │   └── index.blade.php
        ├── auth/
        │   ├── forgot-password.blade.php
        │   ├── login.blade.php
        │   ├── register.blade.php
        │   ├── reset-password.blade.php
        │   ├── verify-email-otp.blade.php
        │   └── verify-email.blade.php
        ├── components/
        │   ├── layout/
        │   │   └── admin/
        │   │       ├── breadcrumb.blade.php
        │   │       ├── children.blade.php
        │   │       ├── footer.blade.php
        │   │       ├── header.blade.php
        │   │       └── sidebar.blade.php
        │   └── ui/
        │       ├── badge.blade.php
        │       ├── button.blade.php
        │       ├── card.blade.php
        │       ├── chart.blade.php
        │       ├── checkbox.blade.php
        │       ├── date.blade.php
        │       ├── dropzone.blade.php
        │       ├── editor.blade.php
        │       ├── file.blade.php
        │       ├── google-button.blade.php
        │       ├── image-cropper.blade.php
        │       ├── input.blade.php
        │       ├── modal-confirm.blade.php
        │       ├── notification.blade.php
        │       ├── password.blade.php
        │       ├── select2.blade.php
        │       ├── switch.blade.php
        │       ├── tagify.blade.php
        │       └── textarea.blade.php
        ├── customer-promos/
        │   ├── form.blade.php
        │   └── index.blade.php
        ├── customers/
        │   ├── form.blade.php
        │   └── index.blade.php
        ├── dashboard/
        │   └── index.blade.php
        ├── errors/
        │   ├── 401.blade.php
        │   ├── 403.blade.php
        │   ├── 404.blade.php
        │   ├── 419.blade.php
        │   ├── 429.blade.php
        │   ├── 500.blade.php
        │   ├── 503.blade.php
        │   └── layout.blade.php
        ├── kitchen/
        │   ├── history.blade.php
        │   ├── index.blade.php
        │   ├── print.blade.php
        │   └── show.blade.php
        ├── layouts/
        │   ├── auth/
        │   │   └── main.blade.php
        │   └── backend/
        │       └── main.blade.php
        ├── menu/
        │   ├── form.blade.php
        │   └── index.blade.php
        ├── menugroup/
        │   ├── form.blade.php
        │   └── index.blade.php
        ├── orders/
        │   ├── form.blade.php
        │   ├── index.blade.php
        │   └── show.blade.php
        ├── outlets/
        │   ├── form.blade.php
        │   ├── index.blade.php
        │   └── partials/
        ├── permission/
        │   ├── form.blade.php
        │   └── index.blade.php
        ├── permissiongroup/
        │   ├── form.blade.php
        │   └── index.blade.php
        ├── pos/
        │   └── index.blade.php
        ├── product-stocks/
        │   ├── form.blade.php
        │   └── index.blade.php
        ├── products/
        │   ├── form.blade.php
        │   └── index.blade.php
        ├── product_categories/
        │   ├── form.blade.php
        │   └── index.blade.php
        ├── promo/
        │   ├── form.blade.php
        │   └── index.blade.php
        ├── reports/
        │   ├── index.blade.php
        │   └── show.blade.php
        ├── role/
        │   ├── form.blade.php
        │   ├── index.blade.php
        │   └── permission.blade.php
        ├── setting/
        │   └── form.blade.php
        ├── stock-movements/
        │   ├── form.blade.php
        │   └── index.blade.php
        ├── tables/
        │   ├── form.blade.php
        │   └── index.blade.php
        ├── transactions/
        │   ├── export-pdf.blade.php
        │   ├── index.blade.php
        │   ├── receipt.blade.php
        │   ├── report.blade.php
        │   └── show.blade.php
        ├── user/
        │   ├── form.blade.php
        │   ├── index.blade.php
        │   └── role.blade.php
        └── welcome.blade.php
Directory structure:
└── routes/
    ├── breadcrumbs.php
    ├── console.php
    └── web.php
---

## 🏷️ Naming Convention

Gunakan **bahasa Inggris**.

| ✅ Gunakan | ❌ Hindari |
|---|---|
| Product | Barang |
| Transaction | Penjualan |
| Expense | — |
| Outlet | — |
| Employee | Kasir |

---

## 🔧 Migration Rule

- Seluruh tabel operasional **wajib** memiliki `outlet_id`.
- Foreign key wajib dibuat.
- Gunakan cascade sesuai kebutuhan.

---

## 🚀 Future Features

Project harus mudah dikembangkan menjadi:

- Warehouse
- Transfer Stock
- Purchase Order
- Kitchen Display System
- QR Ordering
- Loyalty Member
- Membership
- Delivery Integration
- Accounting
- Multi Company
- API
- Mobile App

> ⚠️ Jangan membuat desain yang menghambat fitur-fitur di atas.

---

## 🤖 AI Instruction

Saat menghasilkan kode, AI **wajib**:

1. **Selalu pertimbangkan konsep Multi Outlet** — gunakan scope `outlet_id` di query, database migration, dan policy.
2. **Jangan menghapus filter outlet** — pertahankan filter outlet aktif untuk menjaga integritas data antar outlet.
3. **Gunakan Laravel Best Practice** — terapkan Form Request, Service Layer, HasUuid, dan Clean Code.
4. **Ikuti struktur project yang sudah ada** — konsisten dengan penulisan DataTables, Form Request, controller, dan route binding menggunakan UUID.
5. **Jangan membuat duplikasi logic** — manfaatkan service dan helper yang sudah tersedia.
6. **Prioritaskan scalability dibanding shortcut** — pastikan arsitektur modular monolith tetap terjaga kebersihannya.