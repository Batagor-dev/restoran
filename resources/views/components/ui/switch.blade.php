@props([
    'name',
    'id' => null,
    'label' => '',
    'checked' => false,
    'value' => '1',
])

@php
    $switchId = $id ?? $name;
    $hasError = $name && $errors->has($name);

    $isChecked = old($name, $checked);
    if ($isChecked === '0' || $isChecked === 0 || $isChecked === false) {
        $isChecked = false;
    } elseif ($isChecked) {
        $isChecked = true;
    }
@endphp

<div class="w-full">
    @if($label)
        <label for="{{ $switchId }}" class="mb-2 block text-base font-satoshi-medium text-slate-700">
            {{ $label }}
        </label>
    @endif

    <label for="{{ $switchId }}" class="relative inline-flex items-center cursor-pointer">
        <input 
            type="checkbox" 
            id="{{ $switchId }}" 
            name="{{ $name }}" 
            value="{{ $value }}"
            {{ $isChecked ? 'checked' : '' }}
            {{ $attributes->merge([
                'class' => 'sr-only peer'
            ]) }}
        />
        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-slate-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-slate-900"></div>
    </label>

    @if($hasError)
        <span class="mt-1.5 block text-sm font-medium text-red-600">
            {{ $errors->first($name) }}
        </span>
    @endif
</div>
