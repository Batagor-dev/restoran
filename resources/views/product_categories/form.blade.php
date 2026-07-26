@php
    $isEdit = isset($productCategory);
    $title = $isEdit ? 'Edit Kategori Produk' : 'Tambah Kategori Produk';
@endphp

@extends('layouts.backend.main')

@section('title', $title)

@section('content')
<div class="space-y-8 pb-12">
    <x-ui.card>
        <div class="flex items-center justify-between mb-6">
            <h5 class="text-lg font-satoshi-bold text-slate-900 mb-0">{{ $title }}</h5>
        </div>

        <form action="{{ $action }}" method="POST" class="space-y-6">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <div class="grid grid-cols-1 gap-6">
                {{-- Nama Kategori --}}
                <div>
                    <x-ui.input 
                        name="name" 
                        id="name"
                        label="Category Name"
                        :value="old('name', $productCategory->name ?? '')"
                        placeholder="Enter category name"
                        :required="true"
                        :error="$errors->first('name')"
                    />
                </div>

                {{-- Deskripsi --}}
                <div>
                    <label for="description" class="block text-sm font-satoshi-medium text-slate-700 mb-1">
                        Description
                    </label>
                    <textarea name="description" id="description" rows="4"
                        class="w-full rounded-lg border-slate-300 focus:border-slate-500 focus:ring-1 focus:ring-slate-500 @error('description') border-red-500 @enderror"
                        style="padding: 8px 12px;"
                        placeholder="Enter category description">{{ old('description', $productCategory->description ?? '') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Status Active --}}
                <div>
                    <x-ui.checkbox 
                        name="is_active" 
                        id="is_active"
                        label="Active"
                        :checked="old('is_active', $productCategory->is_active ?? true)"
                        value="1"
                    />
                </div>
            </div>

            {{-- Tombol Aksi --}}
            <div class="flex items-center gap-4 pt-4 border-t border-slate-100">
                <x-ui.button type="submit" size="sm">
                    <i></i> Sumbit
                </x-ui.button>
                <x-ui.button type="button" size="sm" style="secondary" onclick="window.location.href='{{ route('product_categories.index') }}'">
                    Cancel
                </x-ui.button>
            </div>
        </form>
    </x-ui.card>
</div>
@endsection