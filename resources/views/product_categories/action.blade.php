<div class="flex items-center justify-center gap-1">
    @can('Product Category Update')
        <a href="{{ route('product_categories.edit', $category->uuid) }}"
            class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 transition-all duration-200"
            title="Edit">
            <i class="ri-edit-line text-sm"></i>
        </a>
    @endcan

    @can('Product Category Delete')
        <form action="{{ route('product_categories.destroy', $category->uuid) }}" method="POST" class="inline">
            @csrf
            @method('DELETE')
            <button type="button" class="delete-btn inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 transition-all duration-200" title="Hapus">
                <i class="ri-delete-bin-line text-sm"></i>
            </button>
        </form>
    @endcan
</div>