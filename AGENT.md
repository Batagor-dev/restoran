# AGENTS.md

# Project Overview

Project Name:
POS CMS Multi Outlet

Stack:
- Laravel 11
- PostgreSQL
- TailwindCSS
- AlpineJS
- Spatie Permission
- Laravel Fortify (Auth)
- Laravel Socialite (OAuth)
- Yajra Laravel DataTables
- Diglactic Laravel Breadcrumbs
- Spatie Laravel Sluggable
- Intervention Image v3
- Laravel Pint (Linter)

Architecture:
Modular Monolith

---

# Goal

Membangun sistem POS + CMS Multi Outlet untuk bisnis F&B.

Sistem harus scalable agar dapat digunakan oleh:

- Single Outlet
- Multi Outlet
- Franchise
- Enterprise

Semua fitur baru wajib mempertimbangkan konsep Multi Outlet.

---

# Multi Outlet Concept

Project menggunakan Single Database Multi Tenant.

Bukan satu database per outlet.

Semua data operasional dipisahkan menggunakan:

```

outlet_id

```

Setiap query wajib menggunakan outlet_id kecuali data global.

---

# Hierarchy

```

Company
│
├── Outlet
│ ├── Employee
│ ├── Stock
│ ├── Transaction
│ ├── Expense
│ ├── Cashier
│ └── Printer

```

---

# Authentication

User login menggunakan email.

Setelah login user memiliki:

```

User
Role
Permission
Accessible Outlets

```

User dapat memiliki lebih dari satu outlet.

Contoh:

```

User A
├── Outlet Bandung
└── Outlet Jakarta

```

Super Admin memiliki akses ke seluruh outlet.

---

# Authorization

Gunakan Role Based Access Control.

Role contoh:

- Super Admin
- Owner
- Manager
- Cashier
- Kitchen
- Waiter

Permission hanya mengatur:

Apa yang boleh dilakukan.

Contoh:

```

create transaction
update product
delete outlet
view report

```

Sedangkan outlet menentukan:

Data mana yang boleh diakses.

---

# Data Scope

Semua query harus mengikuti outlet aktif.

Contoh:

```

Transaction::where('outlet_id', auth()->user()->current_outlet_id);

```

Jangan pernah mengambil seluruh data tanpa filter outlet kecuali Super Admin.

---

# Global Tables

Tidak memiliki outlet_id.

Contoh:

- companies
- roles
- permissions
- users
- categories
- products
- taxes
- units

---

# Outlet Tables

Harus memiliki outlet_id.

Contoh:

- stocks
- transactions
- transaction_items
- expenses
- shifts
- printers
- employees
- cash_drawers

---

# Product Strategy

Product bersifat global.

Harga dan stok dapat berbeda pada setiap outlet.

Gunakan tabel:

```

product_outlets

id
product_id
outlet_id
price
stock
minimum_stock
status

```

Jangan menyimpan stock langsung pada tabel products.

---

# User Outlet Relation

Gunakan many-to-many.

```

users

id
name
email

```

```

outlets

id
company_id
name

```

```

outlet_user

id
user_id
outlet_id
role_id

```

---

# Coding Rules

- Gunakan Service Layer.
- Jangan letakkan business logic di Controller.
- Gunakan Repository bila query kompleks.
- Validasi menggunakan Form Request.
- Gunakan Policy untuk Authorization.
- Hindari Query Builder di Blade.
- Hindari N+1 Query.

---

# Developer Guide & Client Code Patterns

Untuk memudahkan pemahaman dan pengembangan kode, ikuti standar / antarmuka "Client Code" berikut:

### 1. Models & UUID
Setiap model utama wajib memiliki kolom `id` (primary key auto-increment internal) dan `uuid` (untuk public exposure / url).
- Gunakan trait `App\Models\Traits\HasUuid` untuk generate UUID otomatis saat record dibuat.
- Override `getRouteKeyName` untuk binding route menggunakan `uuid`.
- Gunakan SoftDeletes jika model tersebut memerlukan pengamanan data dari penghapusan permanen.
```php
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\SoftDeletes;

class Menu extends Model {
    use HasUuid, SoftDeletes;
    
    public function getRouteKeyName(): string {
        return 'uuid';
    }
}
```

### 2. Yajra DataTables (Server-side Table)
Untuk list data yang kompleks, gunakan Yajra DataTables dengan layout custom Tailwind + Remix Icons.
- Definisikan class DataTable di `app/DataTables/` (contoh: `MenuDataTable.php`).
- Atur CSS styling tabel di method `html()` menggunakan class Tailwind agar seragam dengan UI yang ada.
- Gunakan controller untuk merender class DataTable tersebut:
```php
public function index(MenuDataTable $dataTable) {
    return $dataTable->render('menu.index');
}
```

### 3. Controller & Request Validation
- Pastikan controller bersih dari logic bisnis yang berat.
- Gunakan Form Request khusus untuk validasi data input (contoh: `StoreMenuRequest`).
- Kirim data ke view menggunakan structured array `$this->data`.

### 4. Settings System Helper
Untuk mengakses/menyimpan konfigurasi aplikasi global:
- Gunakan helper `settings()` yang mengembalikan array metadata.
- Atau panggil static method pada model `Setting`:
  - `Setting::getValue('key', $default)` (mendukung deserialisasi otomatis jika bertipe array/JSON).
  - `Setting::setValue(['key1' => 'val1', 'key2' => 'val2'])` (mendukung upsert dan auto-serialize).
  - `Setting::deleteOldFile('key')` untuk membersihkan disk dari file lama jika di-overwrite.

### 5. Breadcrumbs
Aplikasi menggunakan `diglactic/laravel-breadcrumbs`. Setiap halaman/route baru wajib didaftarkan breadcrumb-nya di `routes/breadcrumbs.php` agar mempermudah navigasi user.

### 6. Image Resizing & Compression
- Untuk mengunggah gambar, gunakan `App\Services\ImageService` yang memanfaatkan `Intervention Image v3` untuk kompresi dan scale aspect-ratio secara otomatis guna menghemat penyimpanan dan mengoptimalkan load time.

---

# Naming Convention

Gunakan bahasa Inggris.

Contoh:

Product

Transaction

Expense

Outlet

Employee

Bukan:

Barang

Penjualan

Kasir

---

# Migration Rule

Seluruh tabel operasional wajib memiliki:

```

outlet_id

```

Foreign key wajib dibuat.

Gunakan cascade sesuai kebutuhan.

---

# Future Features

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

Jangan membuat desain yang menghambat fitur-fitur tersebut.

---

# AI Instruction

Saat menghasilkan kode:

- **Selalu pertimbangkan konsep Multi Outlet**: Gunakan scope `outlet_id` di query, database migration, dan policy.
- **Jangan menghapus filter outlet**: Pertahankan filter outlet aktif untuk menjaga integritas data antar outlet.
- **Gunakan Laravel Best Practice**: Terapkan Form Request, Service Layer, HasUuid, dan Clean Code.
- **Ikuti struktur project yang sudah ada**: Konsisten dengan penulisan DataTables, Form Request, controller, dan route binding menggunakan UUID.
- **Jangan membuat duplikasi logic**: Manfaatkan service, helper, dan helper-helper lain yang tersedia.
- **Prioritaskan scalability dibanding shortcut**: Pastikan arsitektur modular monolith tetap terjaga kebersihannya.