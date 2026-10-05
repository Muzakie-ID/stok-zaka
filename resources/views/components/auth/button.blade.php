@props(['type' => 'submit', 'variant' => 'primary'])

<button type="{{ $type }}" {{ $attributes->class([
    'w-full rounded-xl font-semibold py-3 transition active:scale-[0.99]',
    'primary' === $variant
        ? 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-[0_8px_20px_rgba(16,185,129,0.3)]'
        : 'bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 shadow-none',
]) }}>
    {{ $slot }}
</button>