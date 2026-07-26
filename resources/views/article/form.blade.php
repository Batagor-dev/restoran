@php
    $sub_title = ($breadcrumb = Breadcrumbs::current()) ? $breadcrumb->title : 'Articles';

    if (isset($article_data)) {
        $breadcrumbsData = Breadcrumbs::generate(Request::route()->getName(), $article_data);
    } else {
        $breadcrumbsData = Breadcrumbs::generate(Request::route()->getName());
    }
    $breadcrumb_parent = $breadcrumbsData->where('title', '!=', $breadcrumb->title)->last();
@endphp

@extends('layouts.backend.main')

@section('title', 'Article Form')
@section('sub_title', $sub_title)

@section('breadcrumb')
    <x-layout.admin.breadcrumb :breadcrumbs="$breadcrumbsData" />
@endsection

@section('content')
    <form method="POST" action="{{ $action }}" class="space-y-6" enctype="multipart/form-data">
        @isset($article_data) @method('PUT') @endisset
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left Column: Primary Content -->
            <div class="lg:col-span-8 space-y-3">
                <x-ui.card>
                    <h5 class="text-lg font-satoshi-bold text-slate-900 mb-6">{{ $sub_title }}</h5>
                    
                    <div class="space-y-6">
                        <!-- Title -->
                        <x-ui.input 
                            name="title" 
                            label="Title" 
                            placeholder="Article Title" 
                            value="{{ old('title', $article_data->title ?? '') }}"
                            required
                        />

                        <!-- Content -->
                        <x-ui.editor 
                            name="content" 
                            label="Content" 
                            placeholder="Write article content here..." 
                            :value="old('content', $article_data->content ?? '')"
                        />
                    </div>
                </x-ui.card>
            </div>

            <!-- Right Column: Settings & Media -->
            <div class="lg:col-span-4 space-y-6">
                <!-- Cover Image -->
                <x-ui.card>
                    <h5 class="text-lg font-satoshi-bold text-slate-900 mb-4">Cover Image</h5>
                    <x-ui.dropzone 
                        name="image" 
                        accept="image/*"
                        :previewUrl="isset($article_data->image_path) ? asset('storage/'.$article_data->image_path) : null"
                        :required="!isset($article_data)"
                    />
                </x-ui.card>

                <!-- Settings -->
                <x-ui.card>
                    <h5 class="text-lg font-satoshi-bold text-slate-900 mb-4">Settings</h5>
                    
                    <div class="space-y-6">
                        <!-- Category -->
                        <x-ui.select2 
                            name="article_category_id" 
                            label="Category" 
                            placeholder="-- Choose Category --" 
                            :options="$categories->pluck('name', 'id')->toArray()"
                            :value="old('article_category_id', $article_data->article_category_id ?? '')"
                            required
                        />

                        <!-- Published At -->
                        <x-ui.date 
                            name="published_at" 
                            label="Published At" 
                            placeholder="Select publish date" 
                            value="{{ old('published_at', isset($article_data->published_at) ? \Carbon\Carbon::parse($article_data->published_at)->format('Y-m-d') : '') }}"
                            required
                        />

                        <!-- Tags -->
                        <x-ui.tagify 
                            name="tags" 
                            label="Tags" 
                            placeholder="Add tags..." 
                            value="{{ old('tags', $article_data->tags ?? '') }}"
                        />

                        <!-- Highlight -->
                        <div>
                            <x-ui.switch 
                                name="highlite" 
                                label="Highlight Article" 
                                value="1"
                                :checked="old('highlite', $article_data->highlite ?? false) ? true : false"
                            />
                        </div>
                    </div>
                </x-ui.card>
            </div>
        </div>

        <!-- Form Actions (Outside Card) -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <x-ui.button type="button" font="medium" size="sm" style="secondary" onclick="window.location.href='{{ $breadcrumb_parent?->url ?? route('article.index') }}'">
                Cancel
            </x-ui.button>
            <x-ui.button type="submit" font="bold" size="sm">
                Submit
            </x-ui.button>
        </div>
    </form>
@endsection

@push('scripts')
    {{-- SweetAlert otomatis --}}
    @if(session('success'))
        <script>
            Swal.fire({ icon: 'success', title: 'Success', text: "{{ session('success') }}" });
        </script>
    @endif

    @if(session('error'))
        <script>
            Swal.fire({ icon: 'error',   title: 'Error',  text: "{{ session('error') }}" });
        </script>
    @endif
@endpush
