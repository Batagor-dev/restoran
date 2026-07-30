<div class="flex items-center justify-center gap-1">
    @can('Stock Movement Delete')
        <form action="{{ route('stock-movements.destroy', $movement->uuid) }}" method="POST" class="inline">
            @csrf
            @method('DELETE')
            <button type="button" class="delete-btn inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 transition-all duration-200" title="Delete">
                <i class="ri-delete-bin-line text-sm"></i>
            </button>
        </form>
    @endcan
</div>