<div class="pb-20">
    <!-- Header & Search -->
    <div class="sticky top-14 bg-gray-50 pt-4 pb-2 z-10">
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-2xl font-bold text-gray-800">Stok Gudang</h1>
            <div class="badge badge-primary badge-outline">{{ count($hps) }} Unit</div>
        </div>

        <div class="relative">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari HP atau IMEI..." class="input input-bordered w-full pl-10 rounded-xl shadow-sm" />
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 absolute left-3 top-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
        </div>
    </div>

    <!-- List Stok -->
    <div class="space-y-3 mt-2">
        @forelse($hps as $hp)
            <div class="card bg-white shadow-sm border border-gray-100 rounded-xl overflow-hidden">
                <div class="card-body p-4">
                    <!-- Klik area ini untuk buka detail -->
                    <div class="cursor-pointer" wire:click="$dispatch('open-detail-hp', { id: {{ $hp->id }} })">
                        <div class="flex justify-between items-start">
                            <div>
                                <h2 class="card-title text-base font-bold text-gray-800">{{ $hp->merk_model }}</h2>

                                <p class="text-xs text-gray-500 font-mono mt-1">IMEI: {{ $hp->imei }}</p>
                            </div>
                            <div class="badge {{ $hp->status == 'READY' ? 'badge-success' : 'badge-warning' }} badge-sm text-white">
                                {{ $hp->status }}
                            </div>
                        </div>
                        <div class="divider my-2"></div>
                    </div>

                    <!-- Warna & Minus -->
                    <div class="flex flex-wrap gap-1 mb-2">
                        @if($hp->warna)
                            <span class="badge badge-xs badge-ghost">{{ $hp->warna }}</span>
                        @endif
                        @if($hp->keterangan_minus)
                            <span class="badge badge-xs badge-error badge-outline">Minus: {{ $hp->keterangan_minus }}</span>
                        @endif
                    </div>

                    <div class="flex justify-between items-center">
                        @if(auth()->user()?->isAdmin())
                        <div class="text-xs text-gray-400">
                            Sumber: {{ $hp->sumber_beli ?? '-' }}
                        </div>
                        
                        @if($hp->modalBelumDiisi())
                        <!-- Modal belum diisi (biasanya inputan karyawan) -->
                        <div class="text-right">
                            <div class="badge badge-warning badge-sm text-white mb-0.5">⏳ Modal belum diisi</div>
                            @if($hp->pelacak)
                            <div class="text-[10px] text-gray-400">Diinput: {{ $hp->pelacak->name }}</div>
                            @endif
                        </div>
                        @else
                        <!-- Total Modal Selalu Terlihat -->
                        <div class="text-right">
                            <p class="text-xs text-gray-400 mb-0.5">Total Modal</p>
                            <div class="font-bold text-blue-600">
                                Rp {{ number_format($hp->total_modal, 0, ',', '.') }}
                            </div>
                        </div>
                        @endif
                        @else
                        <!-- Karyawan: info sensitif (modal/sumber) disembunyikan -->
                        <div class="text-xs text-gray-400">
                            @if($hp->warna)<span class="mr-2">{{ $hp->warna }}</span>@endif
                            @if($hp->keterangan_minus)<span>Minus: {{ $hp->keterangan_minus }}</span>@endif
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-10">
                <div class="bg-gray-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
                </div>
                <h3 class="font-bold text-gray-500">Gudang Kosong</h3>
                <p class="text-sm text-gray-400 mt-1">Belum ada stok HP masuk.</p>
                <button onclick="modal_input_stok.showModal()" class="btn btn-sm btn-primary mt-4">Tambah Stok</button>
            </div>
        @endforelse
    </div>
</div>
