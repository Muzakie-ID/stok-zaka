<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $title ?? 'Bisnis HP' }}</title>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 min-h-screen pb-24"> <!-- pb-24 untuk space bottom nav -->
    
    <!-- Top Bar (pasangan bottom nav) -->
    @auth
    <div class="fixed top-0 left-1/2 -translate-x-1/2 w-full max-w-md z-50 h-14 px-4 flex items-center justify-between gap-2 bg-white/80 backdrop-blur-lg border-b border-gray-100">
        <span class="text-xs font-semibold text-slate-500 truncate">
            {{ auth()->user()->name }} &middot; {{ auth()->user()->roleLabel() }}
        </span>
        <div class="flex items-center gap-2 shrink-0">
            @if(auth()->user()?->isAdmin())
            <a href="/label" wire:navigate title="Label Barcode"
               class="w-9 h-9 rounded-full bg-white/80 border border-slate-100 flex items-center justify-center text-slate-500 hover:text-emerald-600 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" /></svg>
            </a>
            <a href="/saldo-karyawan" wire:navigate title="Saldo Karyawan"
               class="w-9 h-9 rounded-full bg-white/80 border border-slate-100 flex items-center justify-center text-slate-500 hover:text-emerald-600 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7 1v2" /></svg>
            </a>
            <a href="/kelola-user" wire:navigate title="Kelola user"
               class="w-9 h-9 rounded-full bg-white/80 border border-slate-100 flex items-center justify-center text-slate-500 hover:text-emerald-600 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
            </a>
            @else
            <a href="/saldo-saya" wire:navigate title="Saldo upah saya"
               class="w-9 h-9 rounded-full bg-white/80 border border-slate-100 flex items-center justify-center text-slate-500 hover:text-emerald-600 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7 1v2" /></svg>
            </a>
            @endif
            <a href="/profil" title="Profil &amp; keluar"
               class="w-9 h-9 rounded-full bg-emerald-600 text-white flex items-center justify-center text-xs font-bold shadow-[0_4px_12px_rgba(16,185,129,0.3)]">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </a>
        </div>
    </div>
    @endauth

    <!-- Main Content -->
    <main class="container mx-auto px-4 max-w-md pt-[72px]">
        {{ $slot }}
    </main>

    <!-- Bottom Navigation -->
    <div class="fixed bottom-0 w-full max-w-md left-1/2 -translate-x-1/2 bg-white/80 backdrop-blur-lg border-t border-gray-100 z-50 h-16 flex justify-around items-center shadow-[0_-8px_30px_rgb(0,0,0,0.04)]">
        <!-- Home -->
        <a href="/" wire:navigate class="flex flex-col items-center justify-center w-full h-full {{ request()->is('/') ? 'text-emerald-600' : 'text-slate-400 hover:text-emerald-600' }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
            <span class="text-[10px] font-semibold">Home</span>
        </a>
        
        <!-- Stok -->
        <a href="/stok" wire:navigate class="flex flex-col items-center justify-center w-full h-full {{ request()->is('stok*') ? 'text-emerald-600' : 'text-slate-400 hover:text-emerald-600' }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>
            <span class="text-[10px] font-semibold">Stok</span>
        </a>

        <!-- Penjualan -->
        <a href="/penjualan" wire:navigate class="flex flex-col items-center justify-center w-full h-full {{ request()->is('penjualan*') ? 'text-emerald-600' : 'text-slate-400 hover:text-emerald-600' }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <span class="text-[10px] font-semibold">Jual</span>
        </a>

        <!-- Add Button (Center, semua user bisa tambah stok) -->
        <div class="relative -top-6">
            <button onclick="modal_input_stok.showModal()" class="bg-emerald-600 text-white rounded-2xl h-12 w-12 shadow-[0_8px_20px_rgba(16,185,129,0.3)] grid place-items-center hover:bg-emerald-700 transition-all active:scale-90 border-4 border-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            </button>
        </div>


        @if(auth()->user()?->isAdmin())
        <!-- Service -->
        <a href="/service" wire:navigate class="flex flex-col items-center justify-center w-full h-full {{ request()->is('service*') ? 'text-emerald-600' : 'text-slate-400 hover:text-emerald-600' }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <span class="text-[10px] font-semibold">Service</span>
        </a>

        <!-- Keuangan -->
        <a href="/keuangan" wire:navigate class="flex flex-col items-center justify-center w-full h-full {{ request()->is('keuangan*') ? 'text-emerald-600' : 'text-slate-400 hover:text-emerald-600' }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="text-[10px] font-semibold">Uang</span>
        </a>

        <!-- Laporan -->
        <a href="/laporan" wire:navigate class="flex flex-col items-center justify-center w-full h-full {{ request()->is('laporan*') ? 'text-emerald-600' : 'text-slate-400 hover:text-emerald-600' }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
            <span class="text-[10px] font-semibold">Laporan</span>
        </a>
        @endif
    </div>

    <!-- Global Modal Input Stok (semua user: karyawan input tanpa modal) -->
    <livewire:input-stok />
    
    @if(auth()->user()?->isAdmin())
    <!-- Global Modal Detail HP (admin saja) -->
    <livewire:detail-hp />
    @endif

</body>
</html>