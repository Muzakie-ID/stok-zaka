@props(['name', 'label', 'type' => 'text', 'autocomplete' => null, 'inputmode' => null])

<div>
    <label for="{{ $name }}" class="block text-sm font-semibold text-slate-700 mb-1">{{ $label }}</label>
    <input id="{{ $name }}"
           name="{{ $name }}"
           type="{{ $type }}"
           @if ($name !== 'password') value="{{ old($name) }}" @endif
           @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
           @if ($inputmode) inputmode="{{ $inputmode }}" @endif
           {{ $attributes->class([
               'w-full rounded-xl border px-4 py-3 text-sm outline-none transition',
               'focus:border-emerald-500 focus:ring-emerald-500',
               'border-red-400' => $errors->has($name),
               'border-slate-200' => ! $errors->has($name),
           ]) }}>
    @error($name)
        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
    @enderror
</div>