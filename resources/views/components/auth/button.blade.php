@props(['type' => 'submit'])

<button type="{{ $type }}" {{ $attributes->class([
    'w-full rounded-xl font-semibold py-3 transition',
    'bg-emerald-600 hover:bg-emerald-700 text-white shadow-[0_8px_20px_rgba(16,185,129,0.3)] active:scale-[0.99]',
]) }}>
    {{ $slot }}
</button>