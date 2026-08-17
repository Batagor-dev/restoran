@php
    $title = 'Point of Sale';
@endphp

@extends('layouts.backend.main')

@section('title', $title)

@section('content')
    <div class="pos-container" x-data="posApp()" x-init="init()">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Left: Product List (2/3) --}}
            <div class="lg:col-span-2 space-y-4">
                {{-- Toolbar --}}
                <div
                    class="flex items-center justify-between gap-4 bg-white p-4 rounded-xl shadow-sm border border-slate-200">
                    <div class="flex items-center gap-3 flex-1">
                        <div class="relative flex-1">
                            <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input type="text" x-model="search" @input="searchProducts()" placeholder="Search product..."
                                class="w-full pl-9 pr-4 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 placeholder:text-slate-400 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200 transition-all">
                        </div>
                        <select x-model="category" @change="filterProducts()" class="select2-category w-[150px]">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        <button @click="toggleFavorites()" class="px-4 py-2 rounded-lg border transition-all duration-200"
                            :class="showFavorites ? 'bg-yellow-50 border-yellow-400 text-yellow-600' : 'border-slate-300 hover:bg-slate-50'">
                            <i class="ri-star-line"></i>
                            <span x-text="showFavorites ? 'Favorites' : 'All'"></span>
                        </button>
                    </div>
                    <span class="text-sm text-slate-500" x-text="products.length + ' products'"></span>
                </div>

                {{-- Product Grid --}}
                <div
                    class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4 max-h-[calc(100vh-300px)] overflow-y-auto p-1">
                    <template x-for="product in products" :key="product.id">
                        <div @click="addToCart(product)"
                            class="bg-white rounded-xl shadow-sm border border-slate-200 hover:shadow-md transition-all duration-200 cursor-pointer group">
                            <div class="relative aspect-square bg-slate-100 rounded-t-xl overflow-hidden">
                                <img :src="product.image ? '/storage/' + product.image : '/assets/img/no-image.png'"
                                    :alt="product.name"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200">
                                <button @click.stop="toggleFavorite(product)"
                                    class="absolute top-2 right-2 w-8 h-8 bg-white/90 backdrop-blur rounded-full flex items-center justify-center shadow-md hover:bg-white transition-all">
                                    <i class="text-lg"
                                        :class="product.is_favorite ? 'ri-star-fill text-yellow-400' : 'ri-star-line text-slate-400'"></i>
                                </button>
                                <span class="absolute bottom-2 left-2 px-2 py-0.5 bg-black/70 text-white text-xs rounded">
                                    <span x-text="product.category.name"></span>
                                </span>
                            </div>
                            <div class="p-3">
                                <h6 class="font-satoshi-medium text-sm text-slate-900 truncate" x-text="product.name"></h6>
                                <p class="text-sm font-satoshi-bold text-slate-900 mt-1">
                                    Rp <span x-text="formatNumber(product.price)"></span>
                                </p>
                            </div>
                        </div>
                    </template>
                    <template x-if="products.length === 0">
                        <div class="col-span-full text-center py-12 text-slate-400">
                            <i class="ri-inbox-line text-4xl block mb-2"></i>
                            <p>No products found</p>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Right: Cart & Order Info (1/3) --}}
            <div class="space-y-4">
                {{-- Cart --}}
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                    <div class="flex items-center justify-between mb-4">
                        <h6 class="font-satoshi-bold text-slate-900">Order</h6>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-slate-500" x-text="cart.length + ' items'"></span>
                            <button @click="clearCart()" class="text-xs text-red-500 hover:text-red-700 transition"
                                x-show="cart.length > 0">
                                Clear All
                            </button>
                        </div>
                    </div>

                    {{-- Cart Items --}}
                    <div class="max-h-80 overflow-y-auto space-y-2">
                        <template x-for="(item, index) in cart" :key="index">
                            <div class="flex items-center gap-3 p-2 bg-slate-50 rounded-lg">
                                <div class="w-12 h-12 rounded-lg bg-slate-200 flex-shrink-0 overflow-hidden">
                                    <img :src="item.image ? '/storage/' + item.image : '/assets/img/no-image.png'"
                                        :alt="item.name" class="w-full h-full object-cover">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-satoshi-medium text-slate-900 truncate" x-text="item.name"></p>
                                    <p class="text-xs text-slate-500">
                                        Rp <span x-text="formatNumber(item.price)"></span>
                                    </p>
                                    {{-- NOTES INPUT --}}
                                    <input type="text" x-model="item.notes" placeholder="Add note..."
                                        class="w-full text-xs border border-slate-200 rounded px-2 py-0.5 mt-1 focus:border-slate-400 focus:ring-0">
                                </div>
                                <div class="flex items-center gap-1">
                                    <button @click="updateCartItem(index, -1)"
                                        class="w-6 h-6 rounded bg-slate-200 hover:bg-slate-300 flex items-center justify-center text-sm">-</button>
                                    <span class="w-8 text-center text-sm font-satoshi-medium" x-text="item.quantity"></span>
                                    <button @click="updateCartItem(index, 1)"
                                        class="w-6 h-6 rounded bg-slate-200 hover:bg-slate-300 flex items-center justify-center text-sm">+</button>
                                    <button @click="removeFromCart(index)" class="ml-1 text-red-400 hover:text-red-600">
                                        <i class="ri-delete-bin-line text-sm"></i>
                                    </button>
                                </div>
                            </div>
                        </template>
                        <template x-if="cart.length === 0">
                            <div class="text-center py-8 text-slate-400">
                                <i class="ri-shopping-cart-line text-3xl block mb-2"></i>
                                <p class="text-sm">Cart is empty</p>
                            </div>
                        </template>
                    </div>

                    {{-- Promo Info --}}
                    <div x-show="promoMessage" class="text-xs text-green-600 mt-1" x-text="promoMessage"></div>

                    {{-- Cart Summary --}}
                    <div class="mt-4 pt-4 border-t border-slate-200 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500">Subtotal</span>
                            <span class="font-satoshi-medium">Rp <span x-text="formatNumber(subtotal)"></span></span>
                        </div>
                        <div class="flex justify-between text-sm" x-show="discount > 0">
                            <span class="text-slate-500">Discount</span>
                            <span class="font-satoshi-medium text-green-600">-Rp <span
                                    x-text="formatNumber(discount)"></span></span>
                        </div>
                        <div class="flex justify-between text-sm" x-show="tax > 0">
                            <span class="text-slate-500">Tax (10%)</span>
                            <span class="font-satoshi-medium">Rp <span x-text="formatNumber(tax)"></span></span>
                        </div>
                        <div class="flex justify-between text-lg font-satoshi-bold pt-2 border-t border-slate-200">
                            <span>Total</span>
                            <span class="text-slate-900">Rp <span x-text="formatNumber(grandTotal)"></span></span>
                        </div>
                    </div>

                    {{-- Promo --}}
                    <div class="mt-3 flex gap-2">
                        <input type="text" x-model="promoCode" placeholder="Promo code" :disabled="discount > 0"
                            class="flex-1 px-3 py-1.5 text-sm rounded-lg border border-slate-300 bg-white text-slate-900 placeholder:text-slate-400 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200 transition-all disabled:bg-slate-100 disabled:text-slate-500">

                        <button x-show="discount <= 0" @click="applyPromo()"
                            class="px-4 py-1.5 bg-slate-900 text-white text-sm rounded-lg hover:bg-slate-800 transition">
                            Apply
                        </button>

                        <button x-show="discount > 0" @click="removePromo()"
                            class="px-4 py-1.5 bg-red-500 text-white text-sm rounded-lg hover:bg-red-600 transition">
                            Remove
                        </button>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <button @click="processOrder()"
                            class="w-full py-2.5 bg-slate-900 text-white rounded-lg hover:bg-slate-800 transition font-satoshi-medium"
                            :disabled="cart.length === 0">
                            Process Order
                        </button>
                        <button @click="clearCart()"
                            class="w-full py-2.5 border border-slate-300 text-slate-600 rounded-lg hover:bg-slate-50 transition font-satoshi-medium">
                            Cancel
                        </button>
                    </div>
                </div>

                {{-- Customer --}}
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                    <h6 class="font-satoshi-bold text-slate-900 mb-2">Customer</h6>
                    <input type="text" x-model="customerName" placeholder="Enter customer name..."
                        class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 bg-white text-slate-900 placeholder:text-slate-400 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200 transition-all">
                </div>

                {{-- Table --}}
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                    <h6 class="font-satoshi-bold text-slate-900 mb-2">Table</h6>
                    <select x-model="tableId" id="select-table" class="select2 w-full">
                        <option value="">No Table</option>
                        @foreach($tables as $table)
                            <option value="{{ $table->id }}">{{ $table->number_table }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Payment Method Summary --}}
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500">Payment</span>
                    <span class="font-satoshi-medium capitalize" x-text="paymentMethod"></span>
                </div>

                {{-- Payment Method --}}
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 mt-4">
                    <h6 class="font-satoshi-bold text-slate-900 mb-2">Payment Method</h6>
                    <div class="grid grid-cols-4 gap-2">
                        <button @click="paymentMethod = 'cash'"
                            class="py-2 px-3 rounded-lg border text-sm font-satoshi-medium transition-all duration-200"
                            :class="paymentMethod === 'cash' ? 'bg-slate-900 text-white border-slate-900' : 'border-slate-300 hover:bg-slate-50'">
                            <i class="ri-money-dollar-circle-line block text-xl mb-1"></i>
                            Cash
                        </button>
                        <button @click="paymentMethod = 'qris'"
                            class="py-2 px-3 rounded-lg border text-sm font-satoshi-medium transition-all duration-200"
                            :class="paymentMethod === 'qris' ? 'bg-slate-900 text-white border-slate-900' : 'border-slate-300 hover:bg-slate-50'">
                            <i class="ri-qr-code-line block text-xl mb-1"></i>
                            QRIS
                        </button>
                        <button @click="paymentMethod = 'debit'"
                            class="py-2 px-3 rounded-lg border text-sm font-satoshi-medium transition-all duration-200"
                            :class="paymentMethod === 'debit' ? 'bg-slate-900 text-white border-slate-900' : 'border-slate-300 hover:bg-slate-50'">
                            <i class="ri-bank-card-line block text-xl mb-1"></i>
                            Debit
                        </button>
                        <button @click="paymentMethod = 'credit'"
                            class="py-2 px-3 rounded-lg border text-sm font-satoshi-medium transition-all duration-200"
                            :class="paymentMethod === 'credit' ? 'bg-slate-900 text-white border-slate-900' : 'border-slate-300 hover:bg-slate-50'">
                            <i class="ri-mastercard-line block text-xl mb-1"></i>
                            Credit
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function () {
            // Inisialisasi Category dropdown
            $('.select2-category').select2({
                width: '150px',
                placeholder: 'All Categories',
                allowClear: true
            });

            // Inisialisasi Table dropdown
            $('#select-table').select2({
                width: '100%',
                placeholder: 'No Table',
                allowClear: true
            });

            // SYNC Select2 KE ALPINE (VERSION 2 - LEBIH AMAN)
            $('#select-table').on('change', function () {
                var val = $(this).val();
                // Cari Alpine component
                var component = document.querySelector('[x-data]').__x;
                if (component) {
                    component.$data.tableId = val;
                    console.log('Table ID updated to:', val);
                }
            });
        });
    </script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('posApp', () => ({
                products: [],
                cart: [],
                categories: @json($categories),
                search: '',
                category: '',
                showFavorites: false,
                promoCode: '',
                discount: 0,
                tax: 0,
                customer: '',
                tableId: '',
                customerName: '',
                subtotal: 0,
                grandTotal: 0,
                paymentMethod: 'cash',
                promoMessage: '',

                init() {
                    this.loadProducts();
                    this.loadCart();

                    // Auto search ketika search berubah
                    this.$watch('search', () => {
                        this.searchProducts();
                    });
                },

                loadProducts() {
                    fetch('/pos/products')
                        .then(res => res.json())
                        .then(data => {
                            this.products = Array.isArray(data) ? data : [];
                            console.log('Products loaded:', this.products.length);
                        })
                        .catch(error => {
                            console.error('Error loading products:', error);
                            this.products = [];
                        });
                },

                searchProducts() {
                    const url = '/pos/products?search=' + encodeURIComponent(this.search) + '&category=' + encodeURIComponent(this.category) + '&favorites=' + (this.showFavorites ? '1' : '0');

                    fetch(url)
                        .then(res => {
                            if (!res.ok) {
                                throw new Error('Failed to load products');
                            }

                            return res.json();
                        })
                        .then(data => {
                            this.products = Array.isArray(data) ? data : [];
                        })
                        .catch(error => {
                            console.error('Error searching products:', error);
                            this.products = [];
                        });
                },

                filterProducts() {
                    console.log('Filtering by category:', this.category);
                    // Panggil searchProducts dengan category yang dipilih
                    this.searchProducts();
                },

                toggleFavorites() {
                    this.showFavorites = !this.showFavorites;
                    this.searchProducts();
                },

                toggleFavorite(product) {
                    fetch('/pos/favorite', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            product_id: product.id
                        })
                    })
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === 'added') {
                                product.is_favorite = true;
                            } else if (data.status === 'removed') {
                                product.is_favorite = false;
                            }

                            // Jika sedang melihat Favorites,
                            // refresh daftar agar produk yang di-unfavorite langsung hilang.
                            if (this.showFavorites) {
                                this.searchProducts();
                            }
                        })
                        .catch(error => {
                            console.error('Error toggling favorite:', error);
                        });
                },

                loadCart() {
                    fetch('/pos/cart')
                        .then(res => res.json())
                        .then(data => {
                            // Data dari backend berbentuk object { product_id: {...} }
                            // Ubah ke array untuk digunakan di Alpine
                            this.cart = Object.values(data || {});
                            console.log('Cart loaded:', this.cart);
                            this.calculateTotals();
                        })
                        .catch(error => {
                            console.error('Error loading cart:', error);
                            this.cart = [];
                        });
                },

                addToCart(product) {
                    if (!product || !product.id) {
                        console.error('Invalid product:', product);
                        return;
                    }

                    const price = parseFloat(product.price) || 0;
                    const quantity = 1;

                    if (price <= 0) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Perhatian',
                            text: 'Product price is 0 or invalid!'
                        });
                        return;
                    }

                    fetch('/pos/cart/add', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            product_id: product.id,
                            quantity: quantity
                        })
                    })
                        .then(res => {
                            if (!res.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return res.json();
                        })
                        .then(data => {
                            if (data.status === 'success') {
                                // Ubah object ke array
                                this.cart = Object.values(data.cart || {});
                                this.cart.forEach(item => {
                                    if (!item.notes) item.notes = '';
                                });
                                console.log('Cart after add:', this.cart);
                                this.calculateTotals();
                            } else {
                                console.error('Error adding to cart:', data);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Gagal menambahkan ke cart: ' + (data.message || 'Unknown error')
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Fetch error:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Error: ' + error.message
                            });
                        });
                },

                updateCartItem(index, delta) {
                    const item = this.cart[index];
                    if (!item) return;

                    const newQty = item.quantity + delta;
                    if (newQty < 1) return;

                    fetch('/pos/cart/update', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            product_id: item.id,
                            quantity: newQty
                        })
                    })
                        .then(res => res.json())
                        .then(data => {
                            this.cart = Object.values(data.cart || {});
                            this.calculateTotals();
                        })
                        .catch(error => {
                            console.error('Error updating cart:', error);
                        });
                },

                removeFromCart(index) {
                    const item = this.cart[index];
                    if (!item) return;

                    fetch('/pos/cart/remove', {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            product_id: item.id
                        })
                    })
                        .then(res => res.json())
                        .then(data => {
                            this.cart = Object.values(data.cart || {});
                            this.calculateTotals();
                        })
                        .catch(error => {
                            console.error('Error removing from cart:', error);
                        });
                },

                clearCart() {
                    if (this.cart.length === 0) return;
                    if (!confirm('Clear all items?')) return;

                    fetch('/pos/cart/clear', {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                        .then(res => res.json())
                        .then(data => {
                            this.cart = [];
                            this.discount = 0;
                            this.promoCode = '';
                            this.promoMessage = '';
                            this.calculateTotals();
                        });
                },

                applyPromo() {
                    if (!this.promoCode.trim()) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Perhatian',
                            text: 'Silakan masukkan kode promo terlebih dahulu.'
                        });
                        return;
                    }

                    fetch('/pos/promo/apply', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            promo_code: this.promoCode.trim()
                        })
                    })
                        .then(res => {
                            if (!res.ok) {
                                throw new Error('Terjadi kesalahan pada server.');
                            }
                            return res.json();
                        })
                        .then(data => {
                            if (data.status === 'success') {
                                this.discount = parseFloat(data.discount) || 0;
                                this.promoMessage = data.scope_message || 'Promo berhasil diterapkan!';
                                this.calculateTotals();

                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success',
                                    text: data.message,
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: data.message || 'Kode promo tidak ditemukan atau sudah tidak berlaku.'
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error applying promo:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Terjadi kesalahan saat memeriksa kode promo.'
                            });
                        });
                },

                removePromo() {
                    fetch('/pos/promo/remove', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === 'success') {
                                this.discount = 0;
                                this.promoCode = '';
                                this.promoMessage = '';

                                this.calculateTotals();

                                Swal.fire({
                                    icon: 'success',
                                    title: 'Promo Removed',
                                    text: 'Promo berhasil dibatalkan.',
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error removing promo:', error);

                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Gagal menghapus promo.'
                            });
                        });
                },

                calculateTotals() {
                    this.subtotal = 0;

                    if (this.cart && this.cart.length > 0) {
                        this.cart.forEach(item => {
                            const price = parseFloat(item.price) || 0;
                            const quantity = parseInt(item.quantity) || 0;

                            this.subtotal += price * quantity;
                        });
                    }

                    // Pajak 10% dari subtotal
                    this.tax = this.subtotal * 0.1;

                    // Total setelah diskon
                    this.grandTotal = Math.max(
                        0,
                        this.subtotal - this.discount + this.tax
                    );

                    console.log('Subtotal:', this.subtotal);
                    console.log('Discount:', this.discount);
                    console.log('Tax:', this.tax);
                    console.log('Grand Total:', this.grandTotal);
                },

                processOrder() {
                    if (this.cart.length === 0) return;

                    const items = this.cart.map(item => ({
                        id: item.id,
                        name: item.name,
                        price: item.price,
                        quantity: item.quantity,
                        notes: item.notes || ''
                    }));

                    const orderData = {
                        customer_name: this.customerName || 'Guest',
                        table_id: this.tableId || null,
                        items: items,
                        subtotal: this.subtotal,
                        discount: this.discount || 0,
                        tax: this.tax,
                        grand_total: this.grandTotal,
                        promo_code: this.promoCode || '',
                        payment_method: this.paymentMethod || 'cash'
                    };

                    fetch('/pos/order', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(orderData)
                    })
                        .then(res => {
                            if (!res.ok) {
                                throw new Error('Server response was not ok');
                            }
                            return res.json();
                        })
                        .then(data => {
                            if (data.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Order Processed!',
                                    text: 'Invoice: ' + data.invoice,
                                    timer: 3000,
                                    showConfirmButton: false
                                });
                                this.clearCart();
                                this.customerName = '';
                                this.tableId = '';
                                this.paymentMethod = 'cash';
                                this.promoCode = '';
                                this.discount = 0;
                                this.promoMessage = '';
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Error processing order: ' + (data.message || 'Unknown error')
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error processing order:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Error processing order: ' + error.message
                            });
                        });
                },

                formatNumber(value) {
                    return new Intl.NumberFormat('id-ID').format(Math.round(value));
                }
            }));
        });
    </script>
@endpush