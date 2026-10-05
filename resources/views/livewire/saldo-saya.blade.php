<div class="pb-24">
    <!-- Header -->
    <div class="bg-white p-4 sticky top-14 z-30 shadow-sm border-b border-gray-200">
        <h1 class="text-xl font-bold text-gray-800">Saldo Upah Saya</h1>
    </div>

    <div class="p-4 space-y-4">
        <!-- Saldo Card -->
        <div class="card bg-gradient-to-br from-emerald-600 to-emerald-800 text-white shadow-xl">
            <div class="card-body p-5">
                <h2 class="text-sm font-medium opacity-80">
                    {{ $periode === 'minggu' ? 'Upah Minggu Ini' : ($periode === 'bulan' ? 'Upah Bulan Ini' : 'Saldo Tercair') }}
                </h2>
                <p class="text-4xl font-bold">Rp {{ number_format($saldo, 0, ',', '.') }}</p>
                <div class="flex gap-4 mt-2">
                    <div class="text-xs">
                        <span class="opacity-70 block">Total Upah</span>
                        <span class="font-semibold text-emerald-200">+ Rp {{ number_format($totalEarn, 0, ',', '.') }}</span>
                    </div>
                    <div class="text-xs">
                        <span class="opacity-70 block">Sudah Dicairkan</span>
                        <span class="font-semibold text-red-200">- Rp {{ number_format($totalPaid, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Riwayat -->
        <div class="card bg-white shadow-sm border border-gray-100">
            <div class="card-body p-4">
                <div class="flex justify-between items-center mb-3 gap-2">
                    <h3 class="font-bold text-gray-800">Riwayat Upah</h3>
                    <div class="join">
                        @foreach (['all' => 'Semua', 'bulan' => 'Bulan Ini', 'minggu' => 'Minggu Ini'] as $val => $label)
                            <button wire:click="$set('periode', '{{ $val }}')"
                                    class="join-item btn btn-xs rounded-none first:rounded-l-lg last:rounded-r-lg border-0 {{ $periode === $val ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-500' }}">
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                </div>

                @if ($riwayat->isEmpty())
                    <p class="text-sm text-gray-400 text-center py-8">
                        {{ $periode === 'all' ? 'Belum ada upah dari admin.' : 'Belum ada upah di periode ini.' }}
                    </p>
                @else
                    <div class="space-y-2">
                        @foreach ($riwayat as $r)
                            <div class="flex justify-between items-center bg-gray-50 rounded-xl px-3 py-2.5" wire:key="r-{{ $r->id }}">
                                <div class="min-w-0">
                                    <div class="text-sm font-bold {{ $r->type === 'earn' ? 'text-emerald-600' : 'text-red-500' }}">
                                        {{ $r->type === 'earn' ? '+' : '-' }} Rp {{ number_format($r->amount, 0, ',', '.') }}
                                    </div>
                                    <div class="text-xs text-gray-400 truncate">{{ $r->description }}</div>
                                </div>
                                <div class="text-right shrink-0 ml-2">
                                    <div class="text-[11px] text-gray-400">{{ $r->created_at->translatedFormat('d M Y') }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
