<div class="pb-24">
    <!-- Header & Filter -->
    <div class="bg-white p-4 sticky top-14 z-30 shadow-sm border-b border-gray-200">
        <h1 class="text-xl font-bold text-gray-800 mb-4">Laporan Penjualan</h1>
        
        <div class="flex gap-2">
            <div class="form-control w-1/2">
                <label class="label py-0"><span class="label-text text-xs">Dari</span></label>
                <input type="date" wire:model.live="startDate" class="input input-sm input-bordered w-full" />
            </div>
            <div class="form-control w-1/2">
                <label class="label py-0"><span class="label-text text-xs">Sampai</span></label>
                <input type="date" wire:model.live="endDate" class="input input-sm input-bordered w-full" />
            </div>
        </div>
    </div>

    <div class="p-4 space-y-4">
        <!-- Summary Cards -->
        <div class="grid grid-cols-2 gap-3">
            <!-- Profit Card (Highlight) -->
            <div class="col-span-2 card bg-gradient-to-r from-green-600 to-green-500 text-white shadow-lg">
                <div class="card-body p-5">
                    <h2 class="text-sm font-medium opacity-90">Total Keuntungan Bersih</h2>
                    <p class="text-3xl font-bold">Rp {{ number_format($totalProfit, 0, ',', '.') }}</p>
                    <div class="flex justify-between items-end mt-2">
                        <div class="text-xs opacity-80">
                            Omzet: Rp {{ number_format($totalOmzet, 0, ',', '.') }}
                        </div>
                        <div class="badge bg-white/20 border-0 text-white text-xs">
                            {{ $totalUnit }} Unit Terjual
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Info -->
            <div class="card bg-white border border-gray-200 shadow-sm">
                <div class="card-body p-3">
                    <div class="text-xs text-gray-500">Total Modal Keluar</div>
                    <div class="font-bold text-gray-700">Rp {{ number_format($totalModal, 0, ',', '.') }}</div>
                </div>
            </div>

            <!-- Margin Info -->
            <div class="card bg-white border border-gray-200 shadow-sm">
                <div class="card-body p-3">
                    <div class="text-xs text-gray-500">Margin Rata-rata</div>
                    @php 
                        $margin = $totalOmzet > 0 ? ($totalProfit / $totalOmzet) * 100 : 0;
                    @endphp
                    <div class="font-bold text-blue-600">{{ number_format($margin, 1) }}%</div>
                </div>
            </div>
        </div>

        <!-- Transaction List -->
        <div>
            <h3 class="font-bold text-gray-800 mb-3">Rincian Transaksi</h3>
            
            @forelse($groupedDetails as $date => $items)
                <div class="mb-4">
                    <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 sticky top-[130px] bg-gray-50 py-1 z-10">
                        {{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}
                    </div>
                    
                    <div class="space-y-2">
                        @foreach($items as $item)
                            <div 
                                wire:click="showDetail({{ $item->id }})"
                                class="card bg-white border border-gray-100 shadow-sm cursor-pointer hover:bg-gray-50 transition-colors"
                            >
                                <div class="card-body p-3">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <div class="font-bold text-gray-800">{{ $item->hp->merk_model ?? 'Item Dihapus' }}</div>
                                            <div class="text-xs text-gray-500">{{ $item->hp->imei ?? '-' }}</div>
                                            @if($item->penjualan->nama_pembeli)
                                                <div class="text-xs text-blue-500 mt-1">
                                                    Pembeli: {{ $item->penjualan->nama_pembeli }}
                                                </div>
                                            @endif
                                        </div>
                                        <div class="text-right">
                                            <div class="font-bold text-green-600">+ Rp {{ number_format($item->laba_rugi, 0, ',', '.') }}</div>
                                            <div class="text-xs text-gray-400">
                                                Jual: {{ number_format($item->harga_jual_unit, 0, ',', '.') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="text-center py-10">
                    <div class="bg-gray-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                    </div>
                    <p class="text-gray-500">Tidak ada data penjualan pada periode ini.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Modal Detail Transaksi -->
    <dialog id="modal_detail_transaksi" class="modal modal-bottom sm:modal-middle" wire:ignore.self>
        <div class="modal-box relative w-full max-w-md mx-auto rounded-t-3xl rounded-b-none sm:rounded-2xl p-0 bg-white shadow-2xl">
            
            <!-- Handle Bar -->
            <div class="w-full flex justify-center pt-3 pb-1" onclick="modal_detail_transaksi.close()">
                <div class="w-12 h-1.5 bg-gray-300 rounded-full"></div>
            </div>

            @if($selectedDetail)
            <div class="p-6 pt-2">
                <!-- Header Info -->
                <div class="mb-6">
                    <h3 class="font-bold text-2xl text-gray-800">{{ $selectedDetail->hp->merk_model }}</h3>
                    <p class="text-sm text-gray-500 font-mono mt-1">{{ $selectedDetail->hp->imei }}</p>
                    
                    <!-- Warna & Minus -->
                    <div class="mt-2 flex flex-wrap gap-2">
                        @if($selectedDetail->hp->warna)
                            <span class="badge badge-ghost text-xs">{{ $selectedDetail->hp->warna }}</span>
                        @endif
                        @if($selectedDetail->hp->keterangan_minus)
                            <span class="badge badge-error badge-outline text-xs">Minus: {{ $selectedDetail->hp->keterangan_minus }}</span>
                        @endif
                    </div>
                </div>

                <!-- Info Transaksi -->
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-100 mb-4">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-xs text-gray-500">Tanggal Jual</span>
                        <span class="font-semibold text-gray-700">{{ $selectedDetail->created_at->translatedFormat('d F Y, H:i') }}</span>
                    </div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-xs text-gray-500">Pembeli</span>
                        <span class="font-semibold text-gray-700">{{ $selectedDetail->penjualan->nama_pembeli ?? '-' }}</span>
                    </div>
                </div>

                <!-- Financial Card -->
                <div class="card bg-blue-50 border border-blue-100 mb-6">
                    <div class="card-body p-4">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-xs text-gray-500">Harga Beli Awal</span>
                            <span class="font-semibold text-gray-700">Rp {{ number_format($selectedDetail->hp->harga_beli_awal, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-xs text-gray-500">Sumber Beli</span>
                            <span class="font-semibold text-gray-700">{{ $selectedDetail->hp->sumber_beli ?? '-' }}</span>
                        </div>
                        
                        <!-- Biaya Service -->
                        @php 
                            $biayaService = $selectedDetail->hp->total_modal - $selectedDetail->hp->harga_beli_awal;
                        @endphp
                        @if($biayaService > 0)
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-xs text-gray-500">Total Biaya Service</span>
                            <span class="font-semibold text-red-500">+ Rp {{ number_format($biayaService, 0, ',', '.') }}</span>
                        </div>
                        @endif

                        <div class="divider my-1"></div>
                        
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-bold text-gray-600">Total Modal</span>
                            <span class="text-sm font-bold text-gray-600">Rp {{ number_format($selectedDetail->modal_terakhir, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-bold text-blue-800">Harga Jual</span>
                            <span class="text-lg font-bold text-blue-800">Rp {{ number_format($selectedDetail->harga_jual_unit, 0, ',', '.') }}</span>
                        </div>
                        
                        <div class="divider my-1"></div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-bold text-gray-800">Keuntungan Bersih</span>
                            <span class="text-xl font-bold {{ $selectedDetail->laba_rugi >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $selectedDetail->laba_rugi >= 0 ? '+' : '' }} Rp {{ number_format($selectedDetail->laba_rugi, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Riwayat Service (Jika Ada) -->
                @if($selectedDetail->hp->services->count() > 0)
                <div class="mb-4">
                    <h4 class="font-bold text-gray-700 text-sm mb-2">Riwayat Service</h4>
                    <div class="space-y-2">
                        @foreach($selectedDetail->hp->services as $service)
                            <div class="flex justify-between items-center bg-white p-2 rounded border border-gray-100 text-xs">
                                <span class="text-gray-600">{{ $service->deskripsi }}</span>
                                <span class="font-bold text-red-500">Rp {{ number_format($service->biaya, 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

            </div>
            @else
            <div class="flex-1 flex items-center justify-center p-10">
                <span class="loading loading-spinner loading-lg text-primary"></span>
            </div>
            @endif
        </div>
        <form method="dialog" class="modal-backdrop bg-black/20 backdrop-blur-sm">
            <button>close</button>
        </form>
    </dialog>

    <script>
        window.addEventListener('open-modal-detail', event => {
            document.getElementById('modal_detail_transaksi').showModal();
        });
    </script>
</div>
