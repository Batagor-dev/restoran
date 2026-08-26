@php
    $sub_title = ($breadcrumb = Breadcrumbs::current()) ? $breadcrumb->title : 'Article Detail';
    $breadcrumbsData = Breadcrumbs::generate(Request::route()->getName(), $article_data);
@endphp

@extends('layouts.backend.main')

@section('title', 'Article Detail')
@section('sub_title', $sub_title)

@section('breadcrumb')
    <x-layout.admin.breadcrumb :breadcrumbs="$breadcrumbsData" />
@endsection

@section('content')
<div class="space-y-8 pb-12">
    <x-ui.card class="p-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6">
            <div>
                <h5 class="text-lg font-satoshi-bold text-slate-900 mb-1">{{ $article_data->title }}</h5>
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500 mt-1">
                    <span><i class="ri-calendar-line mr-1"></i>{{ \Carbon\Carbon::parse($article_data->published_at)->isoFormat('dddd, D MMMM Y') }}</span>
                    <span><i class="ri-folder-line mr-1"></i>{{ $article_data->category->name ?? 'Uncategorized' }}</span>
                    <span><i class="ri-user-line mr-1"></i>{{ $article_data->author->name ?? 'System' }}</span>
                </div>
            </div>

            <div class="flex items-center gap-2 self-start sm:self-auto">
                @can('Article Update')
                    <a href="{{ route('article.edit', $article_data->slug) }}"
                       class="px-4 py-2 border border-slate-300 text-slate-600 rounded-lg hover:bg-slate-50 transition text-sm font-satoshi-medium">
                        <i class="ri-edit-line mr-1"></i> Edit
                    </a>
                @endcan
                <a href="{{ $breadcrumb_parent->url ?? route('article.index') }}"
                   class="px-4 py-2 border border-slate-300 text-slate-600 rounded-lg hover:bg-slate-50 transition text-sm font-satoshi-medium">
                    <i class="ri-arrow-left-line mr-1"></i> Back
                </a>
            </div>
        </div>

        <!-- Cover Image -->
        @if($article_data->image_path)
            <img src="{{ asset('storage/'.$article_data->image_path) }}" alt="{{ $article_data->title }}"
                 class="w-full max-h-[420px] object-cover rounded-xl border border-slate-100 mb-6">
        @endif

        <!-- Content -->
        <div class="prose prose-sm max-w-none text-slate-700 font-satoshi-medium leading-relaxed">
            {!! $article_data->content !!}
        </div>
    </x-ui.card>
</div>
@endsection
