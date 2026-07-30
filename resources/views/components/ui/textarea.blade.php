@props([
    'name',
    'id' => null,
    'label' => null,
    'value' => '',
    'placeholder' => '',
    'rows' => 4,
    'required' => false,
    'error' => null,
])

<div>
    @if($label)
        <label for="{{ $id ?? $name }}" class="block text-sm font-satoshi-medium text-slate-700 mb-1">
            {{ $label }}
            @if($required) <span class="text-red-500">*</span> @endif
        </label>
    @endif

    <textarea
        name="{{ $name }}"
        id="{{ $id ?? $name }}"
        rows="{{ $rows }}"
        {{ $attributes->merge(['class' => 'w-full rounded-lg border-slate-300 focus:border-slate-500 focus:ring-1 focus:ring-slate-500 ' . ($error ? 'border-red-500' : '')]) }}
        placeholder="{{ $placeholder }}"
        @if($required) required @endif
    >{{ old($name, $value) }}</textarea>

    @if($error)
        <p class="mt-1 text-sm text-red-500">{{ $error }}</p>
    @endif
</div>