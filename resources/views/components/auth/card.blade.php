<div>
    {{-- Brand: samakan dengan header halaman utama --}}
    <div class="flex items-center gap-2.5 mb-6">
        <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white grid place-items-center shadow-[0_8px_20px_rgba(16,185,129,0.3)] shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18.75a6 6 0 006-6v-1.5m-6 7.5a6 6 0 01-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 01-3-3V4.5a3 3 0 116 0v8.25a3 3 0 01-3 3z" /></svg>
        </div>
        <div class="min-w-0">
            <p class="text-base font-extrabold text-slate-800 leading-tight truncate">{{ config('app.name', 'Bisnis HP') }}</p>
            <p class="text-xs text-slate-400 truncate">Catatan stok & penjualan HP</p>
        </div>
    </div>

    {{-- Kartu: padanan class "card bg-white shadow-sm border border-gray-100" di halaman dalam --}}
    <div class="card bg-white shadow-sm border border-gray-100">
        <div class="card-body p-5">
            <h1 class="card-title text-lg font-extrabold text-slate-800">{{ $title }}</h1>
            @isset($subtitle)
                <p class="text-sm text-slate-500 -mt-1">{{ $subtitle }}</p>
            @endisset

            {{ $slot }}
        </div>
    </div>
</div>