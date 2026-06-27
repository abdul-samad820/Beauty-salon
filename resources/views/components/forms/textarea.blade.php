@props([
'name',
'label' => null,
'value' => null,
'placeholder' => 'Enter details...',
'rows' => 3,
'required' => false,
])

<div class="space-y-1.5 w-full">
    {{-- Label --}}
    @if($label)
    <label for="{{ $name }}" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
        {{ $label }}
    </label>
    @endif

    {{-- Textarea Container --}}
    <div class="relative rounded-lg shadow-sm">
        <textarea name="{{ $name }}" id="{{ $name }}" rows="{{ $rows }}" class="block w-full rounded-lg border bg-white py-2.5 px-3 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-1 min-h-[80px] transition-all duration-150 dark:bg-slate-950 dark:text-white
            @error($name)
                border-rose-500 focus:border-rose-500 focus:ring-rose-500 text-rose-900 dark:text-rose-400
            @else
                border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-800
            @enderror" placeholder="{{ $placeholder }}" @if($required) required aria-required="true" @endif {{ $attributes }}>{{ old($name, $value) }}</textarea>
    </div>

    {{-- Error Message --}}
    @error($name)
    <p class="mt-1 text-xs font-medium text-rose-600 dark:text-rose-400 flex items-center gap-1">
        <i class="bi bi-exclamation-circle-fill"></i>
        <span>{{ $message }}</span>
    </p>
    @enderror
</div>
