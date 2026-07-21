@props([
    'name' => '',
    'label' => '',
    'placeholder' => 'Select option...',
    'multiple' => false,
    'options' => [],
    'value' => null
])

@php
    $hasError = $name && $errors->has($name);
    $statusClasses = $hasError 
        ? 'border-red-400 bg-red-50/50 text-red-900 focus-within:border-red-500 focus-within:ring-red-100' 
        : 'border-slate-200 bg-slate-50 text-slate-900 focus-within:border-slate-400 focus-within:ring-slate-200';
    
    $inputId = $attributes->get('id', $name);

    // Konversi value ke format array & bersihkan elemen string kosong / null
    $initialValue = [];
    if (isset($value) && $value !== '' && $value !== null) {
        if (is_array($value) || $value instanceof \Illuminate\Support\Collection) {
            $arrayValue = $value instanceof \Illuminate\Support\Collection ? $value->toArray() : $value;
            // Filter hanya nilai yang benar-benar memiliki isi
            $filtered = array_filter($arrayValue, function($item) {
                return $item !== null && trim((string)$item) !== '';
            });
            $initialValue = array_map('strval', array_values($filtered));
        } else {
            $initialValue = [strval($value)];
        }
    }
@endphp

<div class="w-full text-left" 
     x-data="select2Laravel({ 
        multiple: {{ $multiple ? 'true' : 'false' }}, 
        options: {{ json_encode($options) }},
        placeholder: '{{ $placeholder }}',
        selected: {{ json_encode($initialValue) }}
     })">
     
    @if($label)
        <label for="{{ $inputId }}" class="mb-2 block text-base font-satoshi-medium text-slate-700">
            {{ $label }}
        </label>
    @endif

    <div class="relative">
        <!-- Input Box / Trigger Dropdown -->
        <div @click="toggleDropdown()" 
             @click.away="closeDropdown()"
             class="flex min-h-[50px] w-full items-center justify-between rounded-2xl border px-4 py-2.5 text-base font-satoshi-medium cursor-pointer outline-none transition focus-within:ring-2 {{ $statusClasses }}">
            
            <div class="flex flex-wrap gap-1.5 items-center w-full overflow-hidden">
                <template x-if="selected.length === 0">
                    <span class="text-slate-400 font-satoshi">{{ $placeholder }}</span>
                </template>

                <!-- Mode Single (Basic) -->
                <template x-if="!multiple && selected.length > 0 && selected[0] !== ''">
                    <div class="flex items-center justify-between w-full min-w-0">
                        <span class="text-slate-900 truncate" x-text="getLabel(selected[0])"></span>
                        <button type="button" @click.stop="clearSelection()" class="text-slate-400 hover:text-slate-600 font-satoshi-medium ml-2 text-sm leading-none p-1 rounded-md hover:bg-slate-200/50 flex-shrink-0" title="Clear">&times;</button>
                    </div>
                </template>
                <template x-if="!multiple && selected.length > 0 && selected[0] === ''">
                    <span class="text-slate-400 font-satoshi truncate" x-text="getLabel('') || placeholder"></span>
                </template>

                <!-- Mode Multiple -->
                <template x-if="multiple">
                    <div class="flex flex-wrap gap-1.5 items-center">
                        <template x-for="val in selected" :key="val">
                            <div class="flex items-center gap-1 bg-slate-200/70 text-slate-800 text-sm px-2 py-0.5 rounded-lg">
                                <span x-text="getLabel(val)"></span>
                                <button type="button" @click.stop="removeValue(val)" class="text-slate-400 hover:text-slate-600 font-satoshi-medium ml-1 text-xs">&times;</button>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            <svg class="w-4 h-4 text-slate-400 transition-transform shrink-0 ml-2" :class="isOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </div>

        <!-- Hidden input untuk disubmit ke backend -->
        <template x-if="!multiple">
            <input type="hidden" name="{{ $name }}" :value="selected[0] || ''">
        </template>
        <template x-if="multiple">
            <template x-for="val in selected">
                <input type="hidden" name="{{ $name }}[]" :value="val">
            </template>
        </template>

        <!-- Dropdown Area -->
        <div x-show="isOpen" 
             x-transition
             class="absolute z-50 mt-2 w-full bg-white border border-slate-200 rounded-2xl shadow-lg max-h-60 overflow-y-auto p-2" 
             style="display: none;">
            
            <div class="sticky top-0 bg-white pb-2 pt-0.5">
                <input type="text" 
                       x-model="search" 
                       placeholder="Search..." 
                       class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl outline-none focus:border-slate-400 focus:bg-white transition"
                       @click.stop />
            </div>

            <ul class="space-y-0.5">
                <template x-for="opt in filteredOptions" :key="opt.value">
                    <li @click="selectValue(opt.value)"
                        class="px-3 py-2 text-sm rounded-lg cursor-pointer transition flex items-center justify-between"
                        :class="isSelected(opt.value) ? 'bg-slate-100 text-slate-900 font-satoshi-satoshi-medium' : 'text-slate-700 hover:bg-slate-50'">
                        <span x-text="opt.label"></span>
                        
                        <template x-if="isSelected(opt.value)">
                            <svg class="w-4 h-4 text-slate-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </template>
                    </li>
                </template>
                
                <template x-if="filteredOptions.length === 0">
                    <li class="px-3 py-4 text-sm text-center text-slate-400">Data tidak ditemukan</li>
                </template>
            </ul>
        </div>

        @if($hasError)
            <span class="mt-1.5 block text-sm font-medium text-red-600">
                {{ $errors->first($name) }}
            </span>
        @endif
    </div>
</div>

@once
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('select2Laravel', (config) => ({
            multiple: config.multiple,
            options: config.options,
            isOpen: false,
            search: '',
            selected: (Array.isArray(config.selected) ? config.selected : (config.selected ? [config.selected] : [])).map(v => String(v).trim()).filter(v => v !== '' && v !== 'null' && v !== 'undefined'),

            toggleDropdown() { this.isOpen = !this.isOpen; if(this.isOpen) this.search = ''; },
            closeDropdown() { this.isOpen = false; },
            isSelected(val) { return this.selected.includes(String(val)); },
            selectValue(val) {
                val = String(val);
                if (this.multiple) {
                    if (this.isSelected(val)) {
                        this.selected = this.selected.filter(i => i !== val);
                    } else {
                        this.selected.push(val);
                    }
                } else {
                    this.selected = [val];
                    this.closeDropdown();
                }
            },
            clearSelection() {
                this.selected = [];
                this.closeDropdown();
            },
            removeValue(val) {
                this.selected = this.selected.filter(i => i !== String(val));
            },
            get formattedOptions() {
                let items = [];
                if (Array.isArray(this.options)) {
                    items = this.options.map(item => {
                        if (typeof item === 'object' && item !== null) {
                            return { value: String(item.value), label: String(item.label) };
                        }
                        return { value: String(item), label: String(item) };
                    });
                } else {
                    items = Object.keys(this.options).map(key => ({
                        value: String(key),
                        label: String(this.options[key])
                    }));
                }

                return items.sort((a, b) => {
                    if (a.value === '' || a.value === 'null') return -1;
                    if (b.value === '' || b.value === 'null') return 1;
                    return 0;
                });
            },
            getLabel(val) {
                const opt = this.formattedOptions.find(o => o.value === String(val));
                return opt ? opt.label : val;
            },
            get filteredOptions() {
                const formatted = this.formattedOptions;
                if (!this.search) return formatted;
                return formatted.filter(opt => 
                    opt.label.toLowerCase().includes(this.search.toLowerCase())
                );
            }
        }));
    });
</script>
@endonce
