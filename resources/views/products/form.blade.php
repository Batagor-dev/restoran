@php
    $isEdit = isset($product);
    $title = $isEdit ? 'Edit Produk' : 'Tambah Produk';
@endphp

@extends('layouts.backend.main')

@section('title', $title)

@section('content')
<div class="space-y-8 pb-12">
    <x-ui.card>
        <div class="flex items-center justify-between mb-6">
            <h5 class="text-lg font-satoshi-bold text-slate-900 mb-0">{{ $title }}</h5>
        </div>

        <form action="{{ $action }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Kategori --}}
                <div>
                    <x-ui.select2 
                        name="category_id" 
                        id="category_id"
                        label="Kategori"
                        :required="true"
                        :options="$categories"
                        option-value="id"
                        option-label="name"
                        :selected="old('category_id', $product->category_id ?? '')"
                        placeholder="Pilih Kategori"
                        :error="$errors->first('category_id')"
                    />
                </div>

                {{-- Nama Produk --}}
                <div>
                    <x-ui.input 
                        name="name" 
                        id="name"
                        label="Nama Produk"
                        :value="old('name', $product->name ?? '')"
                        placeholder="Masukkan nama produk"
                        :required="true"
                        :error="$errors->first('name')"
                    />
                </div>

                {{-- Harga --}}
                <div>
                    <x-ui.input 
                        name="price_display" 
                        id="price_display"
                        label="Harga (Rp)"
                        :value="old('price', $product->price ?? 0) ? number_format(old('price', $product->price ?? 0), 0, ',', '.') : '0'"
                        placeholder="Masukkan harga produk"
                        :required="true"
                        :error="$errors->first('price')"
                        oninput="formatPrice(this)"
                    />
                    <input type="hidden" name="price" id="price" value="{{ old('price', $product->price ?? 0) }}">
                </div>

                {{-- Gambar --}}
                <div>
                    @if(isset($product) && $product->image)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}"
                                class="h-20 w-20 object-cover rounded-lg border border-slate-200">
                        </div>
                    @endif
                    <x-ui.dropzone
                        name="image"
                        id="image"
                        label="Gambar Produk"
                        accept="image/*"
                        :error="$errors->first('image')"
                    />
                    <p class="mt-1 text-xs text-slate-500">Format: JPEG, PNG, JPG, GIF. Maks: 2MB</p>
                </div>

                {{-- Deskripsi (Full Width) --}}
                <div class="md:col-span-2">
                    <x-ui.editor 
                        name="description" 
                        id="description"
                        label="Deskripsi"
                        :value="old('description', $product->description ?? '')"
                        placeholder="Masukkan deskripsi produk"
                        :error="$errors->first('description')"
                    />
                </div>

                {{-- Status Active --}}
                <div>
                    <x-ui.checkbox 
                        name="is_active" 
                        id="is_active"
                        label="Aktif"
                        :checked="old('is_active', $product->is_active ?? true)"
                        value="1"
                    />
                </div>
            </div>

            {{-- Tombol Aksi --}}
            <div class="flex items-center gap-4 pt-4 border-t border-slate-100">
                <x-ui.button type="submit" size="sm">
                    <i class="ri-save-line mr-1"></i> Simpan
                </x-ui.button>
                <x-ui.button type="button" size="sm" style="secondary" onclick="window.location.href='{{ route('products.index') }}'">
                    Batal
                </x-ui.button>
            </div>
        </form>
    </x-ui.card>
</div>
@endsection

@push('scripts')
<script>
    function formatPrice(input) {
        let value = input.value.replace(/\D/g, '');
        if (value === '') {
            input.value = '0';
            document.getElementById('price').value = '0';
            return;
        }
        let number = parseInt(value);
        let formatted = number.toLocaleString('id-ID');
        input.value = formatted;
        document.getElementById('price').value = number;
    }

    document.addEventListener('DOMContentLoaded', function() {
        const priceDisplay = document.getElementById('price_display');
        const priceHidden = document.getElementById('price');
        if (priceDisplay && priceHidden) {
            let raw = priceDisplay.value.replace(/\D/g, '');
            if (raw && raw !== '0') {
                let number = parseInt(raw);
                priceDisplay.value = number.toLocaleString('id-ID');
                priceHidden.value = number;
            }
        }
    });
</script>
@endpush