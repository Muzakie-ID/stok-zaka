@assets
    <script src="/js/thermal-printer.js"></script>
    <script src="/js/label-print.js"></script>
@endassets

<div class="pb-24">
    <!-- Header -->
    <div class="bg-white p-4 sticky top-14 z-30 shadow-sm border-b border-gray-200">
        <div class="flex justify-between items-center">
            <h1 class="text-xl font-bold text-gray-800">Label Barcode</h1>
            <div class="badge badge-outline">{{ count($selected) }} dipilih</div>
        </div>
        <div class="relative mt-3">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari HP atau IMEI..."
                   class="input input-bordered w-full pl-10 rounded-xl shadow-sm" />
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 absolute left-3 top-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
        </div>
    </div>

    <div class="p-4">
        <!-- Aksi -->
        <div class="flex gap-2 mb-4">
            <button wire:click="pilihSemua" class="btn btn-sm btn-outline rounded-xl">Pilih Semua</button>
            <button wire:click="resetPilihan" class="btn btn-sm btn-ghost rounded-xl">Reset</button>
        </div>

        <!-- Print bar -->
        <div class="card bg-white shadow-sm border border-gray-100 mb-4 sticky top-[136px] z-20">
            <div class="card-body p-3 flex-row items-center gap-2">
                <span class="text-xs text-gray-500 flex-1">
                    {{ count($selected) }} label siap cetak
                </span>
                <button onclick="printLabels()" {{ count($selected) === 0 ? 'disabled' : '' }}
                        class="btn btn-sm bg-slate-700 hover:bg-slate-800 text-white rounded-xl border-none gap-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5z" /></svg>
                    Print A4
                </button>
                <button onclick="printBluetooth()" {{ count($selected) === 0 ? 'disabled' : '' }}
                        class="btn btn-sm bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl border-none gap-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M8.288 15.038a5.25 5.25 0 017.424 0M5.106 11.856c3.807-3.808 9.98-3.808 13.788 0M1.924 8.674c5.565-5.565 14.587-5.565 20.152 0M12.53 18.22l-.53.53-.53-.53a.75.75 0 011.06 0z" /></svg>
                    Bluetooth
                </button>
            </div>
        </div>

        <!-- Legenda -->
        <p class="text-[11px] text-gray-400 mb-3 leading-relaxed">
            <span class="badge badge-xs bg-emerald-100 text-emerald-700 border-none align-middle mr-0.5 gap-0.5"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="inline-block w-2.5 h-2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg> Dicetak</span>
            = label sudah pernah di-print. Tekan ikon <span class="font-bold">panah melingkar</span> di kartu jika label hilang &amp; mau cetak ulang.
        </p>

        <!-- Grid unit READY -->
        <div class="grid grid-cols-1 gap-3">
            @foreach ($hps as $hp)
                @php($sudahCetak = $hp->label_printed_at !== null)
                <div wire:key="hp-{{ $hp->id }}"
                     class="card bg-white shadow-sm border-2 rounded-xl overflow-hidden cursor-pointer transition-colors {{ in_array($hp->id, $selected) ? 'border-emerald-500 bg-emerald-50/40' : ($sudahCetak ? 'border-emerald-200' : 'border-gray-100') }}"
                     wire:click="toggle({{ $hp->id }})">
                    <div class="card-body p-4 flex-row items-center gap-3">
                        <input type="checkbox" value="1" {{ in_array($hp->id, $selected) ? 'checked' : '' }}
                               class="checkbox checkbox-sm checkbox-success shrink-0 pointer-events-none" />
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-1.5">
                                <h2 class="font-bold text-gray-800 text-sm truncate">{{ $hp->merk_model }}</h2>
                                @if($sudahCetak)
                                    <span class="badge badge-xs bg-emerald-100 text-emerald-700 border-none shrink-0 gap-0.5"
                                          title="Dicetak {{ $hp->label_printed_at->translatedFormat('d M Y H:i') }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="inline-block w-2.5 h-2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg> Dicetak
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-400 font-mono">{{ $hp->imei }}</p>
                        </div>
                        <div class="text-right shrink-0">
                            <div class="font-mono text-lg font-bold {{ $sudahCetak ? 'text-emerald-500' : 'text-emerald-600' }}">{{ \App\Livewire\LabelBarcode::kodeLabel($hp->imei) }}</div>
                            <div class="text-[10px] text-gray-400">kode label</div>
                        </div>
                        @if($sudahCetak)
                            <button wire:click="konfirmasiReset({{ $hp->id }})" wire:click.stop
                                    class="btn btn-ghost btn-xs btn-square shrink-0 text-gray-400 hover:text-emerald-600"
                                    title="Cetak ulang (hapus penanda)">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Trigger hidden: diklik dari label-print.js setelah print sukses (wire:click lebih andal daripada dispatch global) --}}
    <button wire:click="tandaiSudahCetak" id="mark-printed-trigger" class="hidden" tabindex="-1" aria-hidden="true"></button>

    <!-- ===== Modal konfirmasi reset cetak (setema emerald) ===== -->
    @php($target = $resetTarget)
    <div class="fixed inset-0 z-[60] flex items-end sm:items-center justify-center p-4
                {{ $showResetModal ? '' : 'pointer-events-none' }}"
         @if($showResetModal) @keydown.escape.window="tutupResetModal" @endif>
        {{-- backdrop --}}
        <div class="absolute inset-0 bg-black/40 backdrop-blur-[2px] transition-opacity duration-200
                    {{ $showResetModal ? 'opacity-100' : 'opacity-0' }}"
             wire:click="tutupResetModal"></div>

        {{-- panel --}}
        <div class="relative w-full sm:max-w-sm bg-white rounded-2xl shadow-2xl p-5 transition-all duration-200
                    {{ $showResetModal ? 'opacity-100 translate-y-0 sm:scale-100' : 'opacity-0 translate-y-4 sm:scale-95' }}">
            <div class="flex items-start gap-3">
                <div class="w-11 h-11 rounded-full bg-emerald-100 text-emerald-600 grid place-items-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
                </div>
                <div class="min-w-0">
                    <h3 class="font-bold text-gray-800">Cetak ulang label?</h3>
                    <p class="text-sm text-gray-500 mt-1">
                        Penanda <span class="badge badge-xs bg-emerald-100 text-emerald-700 border-none align-middle gap-0.5"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="inline-block w-2.5 h-2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg> Dicetak</span>
                        untuk <span class="font-semibold text-gray-700">{{ $target?->merk_model ?? '-' }}</span>
                        akan dihapus. Kode label tetap sama, unit akan terlihat belum dicetak lagi.
                    </p>
                </div>
            </div>
            <div class="flex gap-2 mt-5">
                <button wire:click="tutupResetModal" class="btn btn-ghost flex-1 rounded-xl border-gray-200">Batal</button>
                <button wire:click="resetCetak" @if(! $showResetModal) disabled @endif
                        class="btn flex-1 rounded-xl border-none bg-emerald-600 hover:bg-emerald-700 text-white active:scale-[0.98]">
                    Ya, hapus penanda
                </button>
            </div>
        </div>
    </div>

    <!-- ===== Area print (hidden di layar; sumber data untuk label-print.js) ===== -->
    <div id="print-area" class="hidden">
        <div class="label-grid">
            @foreach ($selected as $id)
                @php($hp = \App\Models\Hp::find($id))
                @if ($hp)
                    <div class="label-item">
                        <div class="label-model">{{ $hp->merk_model }}</div>
                        <div class="label-barcode">{!! $this->barcodeSvg($hp->imei) !!}</div>
                        <div class="label-code">{{ \App\Livewire\LabelBarcode::kodeLabel($hp->imei) }}</div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</div>
