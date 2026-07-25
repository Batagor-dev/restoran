@props([
    'name' => '',
    'label' => '',
    'placeholder' => 'Select date...',
    'value' => '',
    'type' => 'date', // date, time, datetime, multiple, range
    'humanFriendly' => false,
    'disabledDates' => [],
    'inline' => false,
    'minDate' => null,
    'maxDate' => null
])

@php
    $hasError = $name && $errors->has($name);
    $statusClasses = $hasError 
        ? 'border-red-400 bg-red-50/50 text-red-900 focus:border-red-500 focus:ring-red-100' 
        : 'border-slate-200 bg-slate-50 text-slate-900 focus:border-slate-400 focus:ring-slate-200';
    
    $inputId = $attributes->get('id', $name);
@endphp

@once
    @push('styles')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
        <style>
            /* Custom Flatpickr Styling to match user's mockup with Slate theme */
            .flatpickr-calendar {
                border-radius: 1.25rem !important;
                border: 1px solid #e2e8f0 !important;
                box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05) !important;
                font-family: 'Satoshi', sans-serif !important;
                background: #ffffff !important;
                width: 320px !important;
                padding: 0.5rem !important;
            }
            .flatpickr-months {
                display: flex !important;
                align-items: center !important;
                justify-content: space-between !important;
                padding: 0.75rem 0.75rem 0.5rem 0.75rem !important;
                position: relative !important;
            }
            .flatpickr-months .flatpickr-month {
                order: 1 !important;
                text-align: left !important;
                margin-left: 0 !important;
                flex: 1 !important;
                height: auto !important;
                position: static !important;
            }
            .flatpickr-current-month {
                position: static !important;
                width: auto !important;
                display: flex !important;
                align-items: center !important;
                gap: 0.25rem !important;
                font-size: 1.125rem !important; /* ~18px */
                font-weight: 700 !important;
                color: #0f172a !important;
                text-align: left !important;
                left: 0 !important;
                padding: 0 !important;
            }
            .flatpickr-current-month .flatpickr-monthDropdown-months {
                font-family: 'Satoshi', sans-serif !important;
                font-weight: 700 !important;
                color: #0f172a !important;
                padding: 0 !important;
                margin: 0 !important;
                appearance: none !important;
                -webkit-appearance: none !important;
                pointer-events: none !important;
                border: none !important;
                background: transparent !important;
                width: auto !important;
                font-size: 1.125rem !important;
            }
            .flatpickr-current-month .numInputWrapper {
                width: auto !important;
            }
            .flatpickr-current-month .numInputWrapper input.numInput {
                font-family: 'Satoshi', sans-serif !important;
                font-weight: 700 !important;
                color: #0f172a !important;
                padding: 0 !important;
                margin: 0 !important;
                border: none !important;
                background: transparent !important;
                pointer-events: none !important;
                font-size: 1.125rem !important;
            }
            .flatpickr-current-month .numInputWrapper span {
                display: none !important;
            }
            .flatpickr-prev-month, .flatpickr-next-month {
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                width: 2rem !important;
                height: 2rem !important;
                border-radius: 9999px !important;
                background-color: #f1f5f9 !important; /* slate-100 */
                color: #475569 !important; /* slate-600 */
                transition: all 0.2s !important;
                cursor: pointer !important;
                position: static !important;
                padding: 0 !important;
            }
            .flatpickr-prev-month {
                order: 2 !important;
                margin-right: 0.5rem !important;
            }
            .flatpickr-next-month {
                order: 3 !important;
            }
            .flatpickr-prev-month:hover, .flatpickr-next-month:hover {
                background-color: #e2e8f0 !important; /* slate-200 */
                color: #0f172a !important;
            }
            .flatpickr-prev-month svg, .flatpickr-next-month svg {
                width: 0.75rem !important;
                height: 0.75rem !important;
                fill: currentColor !important;
            }
            .flatpickr-weekdays {
                padding: 0.5rem 0.75rem 0.25rem 0.75rem !important;
                height: auto !important;
            }
            span.flatpickr-weekday {
                color: #475569 !important;
                font-weight: 500 !important;
                font-size: 0.875rem !important;
                font-family: 'Satoshi', sans-serif !important;
            }
            .flatpickr-days {
                padding: 0.25rem 0.5rem 0.5rem 0.5rem !important;
                width: auto !important;
            }
            .dayContainer {
                width: auto !important;
                min-width: unset !important;
                max-width: unset !important;
                gap: 2px !important;
            }
            .flatpickr-day {
                border-radius: 9999px !important;
                font-family: 'Satoshi', sans-serif !important;
                font-weight: 500 !important;
                font-size: 0.875rem !important;
                color: #1e293b !important;
                border: none !important;
                height: 38px !important;
                line-height: 38px !important;
                max-width: 38px !important;
                margin: 0 !important;
            }
            .flatpickr-day.prevMonthDay, .flatpickr-day.nextMonthDay {
                color: #cbd5e1 !important; /* slate-300 */
            }
            .flatpickr-day:hover, .flatpickr-day:focus {
                background-color: #f1f5f9 !important;
                color: #0f172a !important;
            }
            .flatpickr-day.today {
                border: 1px solid #e2e8f0 !important;
            }
            .flatpickr-day.today:hover {
                background-color: #f1f5f9 !important;
                color: #0f172a !important;
            }
            .flatpickr-day.selected, 
            .flatpickr-day.selected:hover, 
            .flatpickr-day.selected:focus,
            .flatpickr-day.startRange,
            .flatpickr-day.endRange {
                background: #0f172a !important;
                color: #ffffff !important;
            }
        </style>
    @endpush
@endonce

<div class="w-full text-left" 
     x-data="{
        fp: null,
        init() {
            let options = {
                allowInput: true,
                dateFormat: '{{ $type === 'datetime' ? 'Y-m-d H:i' : ($type === 'time' ? 'H:i' : 'Y-m-d') }}',
                enableTime: {{ in_array($type, ['datetime', 'time']) ? 'true' : 'false' }},
                noCalendar: {{ $type === 'time' ? 'true' : 'false' }},
                mode: '{{ in_array($type, ['multiple', 'range']) ? $type : 'single' }}',
                inline: {{ $inline ? 'true' : 'false' }},
                position: 'below',
                time_24hr: true,
            };

            @if($humanFriendly)
                options.altInput = true;
                options.altFormat = '{{ $type === 'datetime' ? 'F j, Y - H:i' : ($type === 'time' ? 'H:i' : 'F j, Y') }}';
                options.altInputClass = 'block w-full font-satoshi-medium rounded-2xl border px-4 py-3 text-base outline-none transition focus:bg-white focus:ring-2 {{ $statusClasses }}';
            @endif

            @if(!empty($disabledDates))
                options.disable = {!! json_encode($disabledDates) !!};
            @endif

            @if($minDate)
                options.minDate = '{{ $minDate }}';
            @endif

            @if($maxDate)
                options.maxDate = '{{ $maxDate }}';
            @endif

            this.fp = flatpickr(this.$refs.dateInput, options);
        }
     }">
    
    @if($label)
        <label for="{{ $inputId }}" class="mb-2 block text-base font-satoshi-medium text-slate-700">
            {{ $label }}
        </label>
    @endif

    <div class="relative">
        <input 
            {{ $attributes->merge([
                'id' => $inputId,
                'name' => $name,
                'value' => $value,
                'placeholder' => $placeholder,
                'class' => 'block w-full font-satoshi-medium rounded-2xl border px-4 py-3 text-base outline-none transition focus:bg-white focus:ring-2 ' . $statusClasses
            ]) }} 
            x-ref="dateInput"
        />
        
        @if(!$inline && $type !== 'time')
            <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                <i class="ri-calendar-line text-xl"></i>
            </div>
        @elseif(!$inline && $type === 'time')
            <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                <i class="ri-time-line text-xl"></i>
            </div>
        @endif
    </div>

    @if($hasError)
        <span class="mt-1.5 block text-sm font-medium text-red-600">
            {{ $errors->first($name) }}
        </span>
    @endif
</div>

@once
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    @endpush
@endonce
