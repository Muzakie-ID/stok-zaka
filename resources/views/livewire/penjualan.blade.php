<div class="pb-20">
    {{-- Flash message (sukses/error) --}}
    <div class="px-4 pt-4">
        @if (session('error'))
            <div class="rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 mb-2" role="alert">
                ⚠️ {{ session('error') }}
            </div>
        @endif
        @if (session('message'))
            <div class="rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm px-4 py-3 mb-2" role="alert">
                ✅ {{ session('message') }}
            </div>
        @endif
    </div>

    {{-- Tabs Mode --}}
    <div class="px-4 pt-4 pb-2">
        <div class="tabs tabs-boxed bg-gray-100 p-1 rounded-xl">
            <a wire:click="$set('viewMode', 'input')" class="tab w-1/2 h-10 transition-all {{ $viewMode == 'input' ? 'tab-active bg-white shadow-sm text-primary font-bold' : 'text-gray-500 hover:text-gray-700' }}">Input Baru</a>
            <a wire:click="$set('viewMode', 'history')" class="tab w-1/2 h-10 transition-all {{ $viewMode == 'history' ? 'tab-active bg-white shadow-sm text-primary font-bold' : 'text-gray-500 hover:text-gray-700' }}">{{ auth()->user()?->isAdmin() ? 'Riwayat & Retur' : 'Riwayat' }}</a>
        </div>
    </div>

    <!-- Modal Konfirmasi Retur -->
    <dialog id="modal_retur" class="modal modal-bottom sm:modal-middle" wire:ignore.self>
        <div class="modal-box relative w-full max-w-md mx-auto rounded-t-3xl rounded-b-none sm:rounded-2xl p-0 bg-white shadow-2xl">
            
            <!-- Handle Bar -->
            <div class="w-full flex justify-center pt-3 pb-1" onclick="modal_retur.close()">
                <div class="w-12 h-1.5 bg-gray-300 rounded-full"></div>
            </div>

            <div class="p-6 pt-2 text-center">
                <div class="w-16 h-16 bg-red-100 text-red-500 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z" /></svg>
                </div>
                
                <h3 class="font-bold text-xl text-gray-800 mb-2">Konfirmasi Retur</h3>
                
                @if($stokToReturn)
                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-100 mb-4 text-left">
                        <div class="text-xs text-gray-400 font-bold uppercase mb-1">Barang yang diretur</div>
                        <div class="font-bold text-gray-800">{{ $stokToReturn->hp->merk_model ?? '-' }}</div>
                        <div class="text-sm text-gray-500 mb-2">{{ $stokToReturn->hp->imei ?? '-' }}</div>
                        <div class="flex justify-between items-center border-t border-gray-200 pt-2">
                            <span class="text-xs text-gray-500">Refund Dana:</span>
                            <span class="font-bold text-red-500">Rp {{ number_format($stokToReturn->harga_jual_unit, 0, ',', '.') }}</span>
                        </div>
                    </div>
                @endif

                <p class="text-sm text-gray-500 mb-6">
                    Tindakan ini akan mengembalikan stok menjadi <b>READY</b> dan mencatat pengeluaran <b>Refund</b> di kas. Lanjutkan?
                </p>

                <div class="grid grid-cols-2 gap-3">
                    <button type="button" onclick="modal_retur.close()" class="btn btn-ghost w-full rounded-xl">Batal</button>
                    <button wire:click="processReturn" class="btn btn-error w-full rounded-xl text-white">Ya, Retur Barang</button>
                </div>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop bg-black/20 backdrop-blur-sm">
            <button>close</button>
        </form>
    </dialog>

    <script>
        window.addEventListener('open-modal-retur', event => {
            document.getElementById('modal_retur').showModal();
        });
        window.addEventListener('close-modal-retur', event => {
            document.getElementById('modal_retur').close();
        });
    </script>

    @if($viewMode == 'input')
        {{-- Header Input --}}
        <div class="navbar bg-base-100 shadow-sm sticky top-14 z-30">
            <div class="flex-1">
                <a class="btn btn-ghost text-xl">
                    @if($step == 1) Pilih Barang @else Checkout @endif
                </a>
            </div>
            <div class="flex-none">
                @if($step == 2)
                    <button wire:click="prevStep" class="btn btn-square btn-ghost">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                @endif
            </div>
        </div>

        <div class="p-4">
        @if($step == 1)
            {{-- STEP 1: PILIH BARANG --}}
            
            {{-- Search --}}
            <div class="form-control mb-4">
                <input type="text" wire:model.live="search" placeholder="Cari Merk/Model atau IMEI..." class="input input-bordered w-full" />
            </div>

            {{-- List Barang --}}
            <div class="grid grid-cols-1 gap-3">
                @forelse($hps as $hp)
                    @php $modalBelumDiisi = $hp->modalBelumDiisi(); @endphp
                    <div
                        @if(!$modalBelumDiisi) wire:click="toggleSelection({{ $hp->id }})" @endif
                        class="card bg-base-100 shadow-md border border-base-200 transition-all {{ $modalBelumDiisi ? 'opacity-60 cursor-not-allowed' : 'cursor-pointer' }} {{ in_array($hp->id, $selectedHps) ? 'ring-2 ring-primary bg-primary/5' : '' }}"
                    >
                        <div class="card-body p-4 flex flex-row items-center justify-between">
                            <div>
                                <h3 class="font-bold text-lg">{{ $hp->merk_model }}</h3>
                                <div class="text-xs text-gray-500">IMEI: {{ $hp->imei }}</div>
                                <div class="flex flex-wrap gap-1 mt-1">
                                    @if($hp->warna)
                                        <span class="badge badge-xs badge-ghost">{{ $hp->warna }}</span>
                                    @endif
                                    @if($hp->keterangan_minus)
                                        <span class="badge badge-xs badge-error badge-outline">Minus: {{ $hp->keterangan_minus }}</span>
                                    @endif
                                    @if($modalBelumDiisi)
                                        <span class="badge badge-warning badge-xs text-white">⏳ Menunggu modal admin</span>
                                    @endif
                                </div>
                                <div class="text-sm font-semibold text-secondary mt-1">
                                    @if(auth()->user()?->isAdmin())
                                    Modal: Rp {{ number_format($hp->total_modal ?? 0, 0, ',', '.') }}
                                    @if($modalBelumDiisi)<span class="badge badge-warning badge-xs text-white ml-1">⏳ Modal belum diisi</span>@endif
                                    @endif
                                </div>
                            </div>
                            <div>
                                @if($modalBelumDiisi)
                                    <span class="text-warning text-xl">🔒</span>
                                @else
                                    <input type="checkbox" class="checkbox checkbox-primary pointer-events-none" {{ in_array($hp->id, $selectedHps) ? 'checked' : '' }} />
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-10 text-gray-500">
                        Tidak ada barang READY yang ditemukan.
                    </div>
                @endforelse
            </div>

            {{-- Bottom Bar --}}
            @if(count($selectedHps) > 0)
                <div class="fixed bottom-16 left-0 right-0 p-4 bg-base-100 border-t border-base-200 z-40 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.1)]">
                    <div class="flex justify-between items-center mb-2">
                        <span class="font-semibold">{{ count($selectedHps) }} Barang dipilih</span>
                    </div>
                    <button wire:click="nextStep" class="btn btn-primary w-full text-lg">
                        Lanjut ke Harga
                    </button>
                </div>
            @endif

        @elseif($step == 2)
            {{-- STEP 2: CHECKOUT & SPLIT HARGA --}}

            <div class="space-y-4">
                {{-- Info Pembeli --}}
                <div class="card bg-base-100 shadow-sm border border-base-200">
                    <div class="card-body p-4">
                        <h3 class="font-bold mb-2">Info Transaksi</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="form-control w-full">
                                <label class="label"><span class="label-text">Nama Pembeli (Opsional)</span></label>
                                <input type="text" wire:model="nama_pembeli" class="input input-bordered w-full" placeholder="Contoh: Pak Budi" />
                            </div>
                            <div class="form-control w-full">
                                <label class="label"><span class="label-text">No. WhatsApp (Opsional)</span></label>
                                <label class="input input-bordered flex items-center gap-2 w-full">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4 text-green-600 shrink-0" fill="currentColor"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38a9.87 9.87 0 004.74 1.21c5.46 0 9.91-4.45 9.91-9.91S17.5 2 12.04 2zm5.83 14.13c-.25.7-1.44 1.33-2.01 1.42-.51.08-1.16.11-1.87-.12-.43-.14-.99-.32-1.7-.63-3-1.3-4.96-4.32-5.11-4.52-.15-.2-1.22-1.62-1.22-3.09 0-1.47.77-2.19 1.04-2.49.27-.3.59-.38.79-.38.2 0 .4.002.57.01.18.008.43-.07.67.51.25.6.84 2.06.91 2.21.08.15.13.33.02.53-.1.2-.15.32-.3.5-.15.17-.32.39-.46.52-.15.15-.31.31-.13.61.18.3.79 1.3 1.69 2.11 1.16 1.03 2.14 1.35 2.44 1.5.3.15.48.13.65-.08.18-.2.75-.87.95-1.17.2-.3.4-.25.67-.15.27.1 1.72.81 2.02.96.3.15.5.23.57.35.08.13.08.73-.17 1.43z"/></svg>
                                    <input type="tel" wire:model="wa_pembeli" class="grow" placeholder="0812xxxx" />
                                </label>
                                @error('wa_pembeli') <span class="text-error text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="form-control w-full mt-2">
                            <label class="label"><span class="label-text font-bold">Total Transaksi (Semua Barang)</span></label>
                            <div class="relative">
                                <input type="text" readonly x-data="{
                                    val: @entangle('total_transaksi'),
                                    format(v) { return v ? new Intl.NumberFormat('id-ID').format(v) : '' }
                                }" :value="format(val)" class="input input-bordered input-lg w-full pl-12 font-bold text-primary bg-gray-100" placeholder="0" />
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-lg font-bold pointer-events-none">Rp</span>
                            </div>
                            <label class="label"><span class="label-text-alt text-gray-500">*Otomatis terhitung dari total harga per unit</span></label>
                        </div>
                        @error('total_transaksi') <span class="text-error text-sm">{{ $message }}</span> @enderror

                        {{-- Realtime Profit Info (khusus admin) --}}
                        @php
                            $totalModal = (float) $hps->sum('total_modal');
                            $estimasiProfit = (float)$total_transaksi - $totalModal;
                        @endphp
                        @if(auth()->user()?->isAdmin())
                        <div class="grid grid-cols-2 gap-4 mt-4 p-3 bg-gray-50 rounded-lg border border-gray-100">
                            <div>
                                <div class="text-xs text-gray-500">Total Modal</div>
                                <div class="font-bold text-gray-700">Rp {{ number_format($totalModal, 0, ',', '.') }}</div>
                            </div>
                            <div class="text-right">
                                <div class="text-xs text-gray-500">Estimasi Profit</div>
                                <div class="font-bold {{ $estimasiProfit >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $estimasiProfit >= 0 ? '+' : '' }} Rp {{ number_format($estimasiProfit, 0, ',', '.') }}
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Rincian Per Item --}}
                <div class="card bg-base-100 shadow-sm border border-base-200">
                    <div class="card-body p-4">
                        <h3 class="font-bold mb-2">Rincian Harga Per Unit</h3>
                        <p class="text-xs text-gray-500 mb-4">Masukkan harga jual untuk masing-masing unit agar keuntungan tercatat akurat per barang.</p>

                        <div class="space-y-4">
                            @foreach($hps as $hp)
                                <div class="p-3 bg-base-200 rounded-lg" wire:key="hp-item-{{ $hp->id }}">
                                    <div class="flex justify-between items-start mb-2">
                                        <div>
                                            <div class="font-bold">{{ $hp->merk_model }}</div>
                                            <div class="text-xs text-gray-500">{{ $hp->imei }}</div>
                                            <div class="flex flex-wrap gap-1 mt-1">
                                                @if($hp->warna)
                                                    <span class="badge badge-xs badge-ghost">{{ $hp->warna }}</span>
                                                @endif
                                                @if($hp->keterangan_minus)
                                                    <span class="badge badge-xs badge-error badge-outline">Minus: {{ $hp->keterangan_minus }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        @if(auth()->user()?->isAdmin())
                                        <div class="badge badge-ghost text-xs">Modal: {{ number_format($hp->total_modal ?? 0, 0, ',', '.') }}</div>
                                        @endif
                                    </div>
                                    <div class="form-control">
                                        <label class="label py-0"><span class="label-text text-xs">Harga Jual Unit Ini</span></label>
                                        <div class="relative">
                                            <input type="text" x-data="{
                                                val: @entangle('harga_jual_items.' . $hp->id).live,
                                                format(v) { return v ? new Intl.NumberFormat('id-ID').format(v) : '' },
                                                update(e) {
                                                    let raw = e.target.value.replace(/[^0-9]/g, '');
                                                    this.val = raw;
                                                    e.target.value = this.format(raw);
                                                }
                                            }" :value="format(val)" @input="update" class="input input-bordered input-sm w-full pl-8" placeholder="0" />
                                            <span class="absolute left-2 top-1/2 -translate-y-1/2 text-gray-400 text-xs font-semibold pointer-events-none">Rp</span>
                                        </div>
                                    </div>
                                    {{-- Kalkulasi Laba Realtime (khusus admin) --}}
                                    @if(auth()->user()?->isAdmin() && isset($harga_jual_items[$hp->id]) && is_numeric($harga_jual_items[$hp->id]))
                                        <div class="text-right mt-1 text-xs">
                                            @php $laba = $harga_jual_items[$hp->id] - ($hp->total_modal ?? 0); @endphp
                                            Laba: <span class="{{ $laba >= 0 ? 'text-success' : 'text-error' }} font-bold">
                                                Rp {{ number_format($laba, 0, ',', '.') }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        
                        {{-- Summary Check --}}
                        @php 
                            $sumItems = 0;
                            foreach($harga_jual_items as $val) {
                                $sumItems += (float) $val;
                            }
                        @endphp
                        <div class="mt-4 p-3 rounded-lg {{ $sumItems == $total_transaksi ? 'bg-success/10 text-success' : 'bg-error/10 text-error' }}">
                            <div class="flex justify-between text-sm font-bold">
                                <span>Total Rincian:</span>
                                <span>Rp {{ number_format($sumItems, 0, ',', '.') }}</span>
                            </div>
                            @if($sumItems != $total_transaksi)
                                <div class="text-xs mt-1">
                                    Selisih: Rp {{ number_format($total_transaksi - $sumItems, 0, ',', '.') }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bottom Action --}}
            <div class="fixed bottom-16 left-0 right-0 p-4 bg-base-100 border-t border-base-200 z-40 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.1)]">
                <button 
                    wire:click="processPenjualan" 
                    class="btn btn-primary w-full text-lg"
                    wire:loading.attr="disabled"
                >
                    <span wire:loading.remove>Simpan Penjualan</span>
                    <span wire:loading>Menyimpan...</span>
                </button>
            </div>
        @endif
    </div>

    @elseif($viewMode == 'history')
        <div class="p-4 space-y-4">
            {{-- Search History --}}
            <div class="relative">
                <input type="text" wire:model.live="historySearch" placeholder="Cari Pembeli, HP, atau IMEI..." class="input input-bordered w-full rounded-xl bg-gray-50 pl-10 focus:bg-white transition-colors" />
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            </div>

            {{-- List History --}}
            @forelse($history as $trx)
                <div class="card bg-white border border-gray-200 shadow-sm rounded-xl overflow-hidden">
                    <div class="card-body p-4 bg-gray-50 border-b border-gray-100">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="font-bold text-gray-800">{{ $trx->nama_pembeli ?: 'Tanpa Nama' }}</div>
                                <div class="text-xs text-gray-500">{{ $trx->created_at->format('d M Y H:i') }}</div>
                                @if($trx->user)
                                    <div class="badge badge-ghost badge-sm mt-1 {{ $trx->user->isAdmin() ? 'text-emerald-700 bg-emerald-50' : 'text-blue-700 bg-blue-50' }}">
                                        🛍 {{ $trx->user->name }}{{ $trx->user->isAdmin() ? ' (Admin)' : '' }}
                                    </div>
                                @endif
                                @if($trx->wa_pembeli)
                                    <a href="{{ $trx->wa_link }}" target="_blank" class="btn btn-xs mt-1 h-6 min-h-6 gap-1 border-green-200 bg-green-50 text-green-700 hover:bg-green-100 hover:border-green-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-3 w-3" fill="currentColor"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38a9.87 9.87 0 004.74 1.21c5.46 0 9.91-4.45 9.91-9.91S17.5 2 12.04 2zm5.83 14.13c-.25.7-1.44 1.33-2.01 1.42-.51.08-1.16.11-1.87-.12-.43-.14-.99-.32-1.7-.63-3-1.3-4.96-4.32-5.11-4.52-.15-.2-1.22-1.62-1.22-3.09 0-1.47.77-2.19 1.04-2.49.27-.3.59-.38.79-.38.2 0 .4.002.57.01.18.008.43-.07.67.51.25.6.84 2.06.91 2.21.08.15.13.33.02.53-.1.2-.15.32-.3.5-.15.17-.32.39-.46.52-.15.15-.31.31-.13.61.18.3.79 1.3 1.69 2.11 1.16 1.03 2.14 1.35 2.44 1.5.3.15.48.13.65-.08.18-.2.75-.87.95-1.17.2-.3.4-.25.67-.15.27.1 1.72.81 2.02.96.3.15.5.23.57.35.08.13.08.73-.17 1.43z"/></svg>
                                        {{ $trx->wa_pembeli }}
                                    </a>
                                @endif
                            </div>
                            <div class="text-right">
                                <div class="font-bold text-primary">Rp {{ number_format($trx->total_transaksi, 0, ',', '.') }}</div>
                                <div class="text-[10px] uppercase font-bold text-gray-400">Total</div>
                            </div>
                        </div>
                    </div>
                    <div class="divide-y divide-gray-100 bg-white">
                        @foreach($trx->details as $dtl)
                            <div class="p-3 flex justify-between items-center group hover:bg-gray-50 transition-colors">
                                <div>
                                    <div class="font-medium text-sm text-gray-700">{{ $dtl->hp->merk_model ?? 'Item Terhapus' }}</div>
                                    <div class="text-xs text-gray-400 family-mono">IMEI: {{ $dtl->hp->imei ?? '-' }}</div>
                                    <div class="text-xs text-green-600 font-semibold mt-1">Rp {{ number_format($dtl->harga_jual_unit, 0, ',', '.') }}</div>
                                </div>
                                <div>
                                    @if($dtl->hp && auth()->user()?->isAdmin())
                                        <button 
                                            wire:click="confirmReturn({{ $dtl->id }})"
                                            class="btn btn-xs btn-outline btn-error rounded-lg"
                                        >
                                            Retur
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="text-center py-10">
                    <div class="text-gray-300 mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
                    </div>
                    <span class="text-gray-400 text-sm">Belum ada riwayat penjualan</span>
                </div>
            @endforelse
        </div>
    @endif
</div>
