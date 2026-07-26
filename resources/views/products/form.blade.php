@php
    $isEdit = isset($product);
    $title = $isEdit ? 'Edit Produk' : 'Tambah Produk';
@endphp

@extends('layouts.backend.main')

@section('title', $title)

@section('content')
<div class="space-y-8 pb-12">
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <div class="flex items-center justify-between mb-6">
            <h5 class="text-lg font-satoshi-bold text-slate-900">{{ $title }}</h5>
        </div>

        <form action="{{ $action }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            {{-- Row 1: Kategori + Nama Produk --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="category_id" class="block text-sm font-satoshi-medium text-slate-700 mb-1">
                        Kategori <span class="text-red-500">*</span>
                    </label>
                    <select name="category_id" id="category_id"
                        class="w-full rounded-lg border-slate-300 focus:border-slate-500 focus:ring-1 focus:ring-slate-500 @error('category_id') border-red-500 @enderror"
                        style="padding: 8px 12px; background: white;" required>
                        <option value="">Pilih Kategori</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}"
                                {{ old('category_id', $product->category_id ?? '') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="name" class="block text-sm font-satoshi-medium text-slate-700 mb-1">
                        Nama Produk <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" id="name" value="{{ old('name', $product->name ?? '') }}"
                        class="w-full rounded-lg border-slate-300 focus:border-slate-500 focus:ring-1 focus:ring-slate-500 @error('name') border-red-500 @enderror"
                        style="padding: 8px 12px;"
                        placeholder="Masukkan nama produk" required>
                    @error('name')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Row 2: Harga + Gambar --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Harga --}}
<div>
    <label for="price" class="block text-sm font-satoshi-medium text-slate-700 mb-1">
        Harga (Rp) <span class="text-red-500">*</span>
    </label>
    <input type="text" name="price_display" id="price_display" 
        value="{{ old('price', $product->price ?? 0) ? number_format(old('price', $product->price ?? 0), 0, ',', '.') : '0' }}"
        class="w-full rounded-lg border-slate-300 focus:border-slate-500 focus:ring-1 focus:ring-slate-500 @error('price') border-red-500 @enderror"
        style="padding: 8px 12px;"
        placeholder="Masukkan harga produk" 
        oninput="formatPrice(this)" required>
    <input type="hidden" name="price" id="price" value="{{ old('price', $product->price ?? 0) }}">
    @error('price')
        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
    @enderror
</div>

                <div>
                    <label for="image" class="block text-sm font-satoshi-medium text-slate-700 mb-1">
                        Gambar Produk
                    </label>
                    @if(isset($product) && $product->image)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}"
                                class="h-16 w-16 object-cover rounded-lg border border-slate-200">
                        </div>
                    @endif
                    <input type="file" name="image" id="image"
                        class="w-full rounded-lg border-slate-300 focus:border-slate-500 focus:ring-1 focus:ring-slate-500 @error('image') border-red-500 @enderror"
                        style="padding: 6px 12px; background: white;"
                        accept="image/*">
                    <p class="mt-1 text-xs text-slate-500">Format: JPEG, PNG, JPG, GIF. Maks: 2MB</p>
                    @error('image')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Row 3: Deskripsi (Full Width) --}}
            <div>
                <label for="description" class="block text-sm font-satoshi-medium text-slate-700 mb-1">
                    Deskripsi
                </label>
                <textarea name="description" id="description" rows="4"
                    class="w-full rounded-lg border-slate-300 focus:border-slate-500 focus:ring-1 focus:ring-slate-500 @error('description') border-red-500 @enderror"
                    style="padding: 8px 12px;"
                    placeholder="Masukkan deskripsi produk">{{ old('description', $product->description ?? '') }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Row 4: Status Active --}}
            <div>
                <div class="flex items-center gap-3">
                    <input type="checkbox" name="is_active" id="is_active" value="1"
                        {{ old('is_active', $product->is_active ?? true) ? 'checked' : '' }}
                        class="rounded border-slate-300 text-slate-600 focus:ring-slate-500 h-4 w-4">
                    <label for="is_active" class="text-sm font-satoshi-medium text-slate-700">
                        Aktif
                    </label>
                </div>
            </div>

            {{-- Tombol Aksi --}}
            <div class="flex items-center gap-3 pt-4 border-t border-slate-200">
                <button type="submit" class="px-6 py-2.5 bg-slate-900 text-white rounded-xl hover:bg-slate-800 transition-all duration-200 text-sm font-satoshi-medium">
                    <i class="ri-save-line mr-1"></i> Simpan
                </button>
                <a href="{{ route('products.index') }}" class="px-6 py-2.5 border border-slate-300 text-slate-600 rounded-xl hover:bg-slate-50 transition-all duration-200 text-sm font-satoshi-medium">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function formatPrice(input) {
        // Hapus semua karakter non-digit
        let value = input.value.replace(/\D/g, '');
        
        // Jika kosong, set ke 0
        if (value === '') {
            input.value = '0';
            document.getElementById('price').value = '0';
            return;
        }
        
        // Konversi ke integer
        let number = parseInt(value);
        
        // Format dengan titik ribuan
        let formatted = number.toLocaleString('id-ID');
        
        // Tampilkan di input
        input.value = formatted;
        
        // Simpan nilai asli (tanpa titik) ke hidden input
        document.getElementById('price').value = number;
    }

    // Inisialisasi harga saat load
    document.addEventListener('DOMContentLoaded', function() {
        const priceDisplay = document.getElementById('price_display');
        const priceHidden = document.getElementById('price');
        
        // Jika ada nilai dari server, format ulang
        if (priceDisplay.value && priceDisplay.value !== '0') {
            let raw = priceDisplay.value.replace(/\D/g, '');
            if (raw) {
                let number = parseInt(raw);
                priceDisplay.value = number.toLocaleString('id-ID');
                priceHidden.value = number;
            }
        }
    });
</script>
@endpush
@endsection