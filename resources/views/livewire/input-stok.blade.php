<div>
    <!-- Modal Toggle dikontrol dari luar via event atau Alpine -->
    <dialog id="modal_input_stok" class="modal modal-bottom sm:modal-middle" wire:ignore.self>
        <div class="modal-box relative w-full max-w-md mx-auto rounded-t-3xl rounded-b-none sm:rounded-2xl p-0 bg-white shadow-2xl">
            
            <!-- Handle Bar (Visual Indicator) -->
            <div class="w-full flex justify-center pt-3 pb-1" onclick="modal_input_stok.close()">
                <div class="w-12 h-1.5 bg-gray-300 rounded-full"></div>
            </div>

            <div class="p-6 pt-2">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-bold text-xl text-gray-800">Tambah Stok</h3>
                    <button type="button" onclick="modal_input_stok.close()" class="btn btn-sm btn-circle btn-ghost text-gray-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <!-- Mode Switcher -->
                <div class="tabs tabs-boxed bg-gray-100 p-1 mb-6">
                    <a wire:click="setMode('satuan')" class="tab w-1/2 {{ $mode == 'satuan' ? 'tab-active bg-white shadow-sm text-primary font-bold' : '' }}">Satuan</a> 
                    <a wire:click="setMode('borongan')" class="tab w-1/2 {{ $mode == 'borongan' ? 'tab-active bg-white shadow-sm text-primary font-bold' : '' }}">Borongan</a>
                </div>
                
                @if($mode == 'satuan')
                    <form wire:submit="save" class="space-y-4">
                        <!-- IMEI -->
                        <div class="form-control w-full">
                            <label class="label py-1">
                                <span class="label-text font-medium text-gray-600">IMEI / Serial Number</span>
                            </label>
                            <div class="relative">
                                <input wire:model="imei" type="text" placeholder="Scan atau ketik..." class="input input-bordered w-full pl-4 pr-12 rounded-xl bg-gray-50 focus:bg-white transition-colors @error('imei') input-error @enderror" />
                                <button type="button" onclick="startScan()" class="absolute right-2 top-1/2 -translate-y-1/2 btn btn-sm btn-ghost btn-square text-gray-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                </button>
                            </div>
                            @error('imei') <span class="text-error text-xs mt-1 ml-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Merk & Model -->
                        <div class="form-control w-full">
                            <label class="label py-1">
                                <span class="label-text font-medium text-gray-600">Merk & Model</span>
                            </label>
                            <input wire:model="merk_model" type="text" placeholder="Contoh: Samsung S23 Ultra" class="input input-bordered w-full rounded-xl bg-gray-50 focus:bg-white transition-colors @error('merk_model') input-error @enderror" />
                            @error('merk_model') <span class="text-error text-xs mt-1 ml-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Warna & Minus -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="form-control w-full">
                                <label class="label py-1">
                                    <span class="label-text font-medium text-gray-600">Warna</span>
                                </label>
                                <input wire:model="warna" type="text" placeholder="Hitam" class="input input-bordered w-full rounded-xl bg-gray-50 focus:bg-white transition-colors" />
                            </div>
                            <div class="form-control w-full">
                                <label class="label py-1">
                                    <span class="label-text font-medium text-gray-600">Minus (Opsional)</span>
                                </label>
                                <input wire:model="keterangan_minus" type="text" placeholder="Lecet, dll" class="input input-bordered w-full rounded-xl bg-gray-50 focus:bg-white transition-colors" />
                            </div>
                        </div>

                        @if(auth()->user()?->isAdmin())
                        <div class="grid grid-cols-2 gap-4">
                            <!-- Harga Beli -->
                            <div class="form-control w-full">
                                <label class="label py-1">
                                    <span class="label-text font-medium text-gray-600">Harga Beli</span>
                                </label>
                                <div class="relative" x-data="{
                                    price: @entangle('harga_beli_awal'),
                                    format(val) { return val ? new Intl.NumberFormat('id-ID').format(val) : '' },
                                    input(e) {
                                        let raw = e.target.value.replace(/[^0-9]/g, '');
                                        this.price = raw;
                                        e.target.value = this.format(raw);
                                    }
                                }">
                                    <input type="text" :value="format(price)" @input="input" placeholder="0" class="input input-bordered w-full pl-10 rounded-xl bg-gray-50 focus:bg-white transition-colors @error('harga_beli_awal') input-error @enderror" />
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-semibold pointer-events-none">Rp</span>
                                </div>
                                @error('harga_beli_awal') <span class="text-error text-xs mt-1 ml-1">{{ $message }}</span> @enderror
                            </div>

                            <!-- Sumber Beli -->
                            <div class="form-control w-full">
                                <label class="label py-1">
                                    <span class="label-text font-medium text-gray-600">Sumber</span>
                                </label>
                                <input wire:model="sumber_beli" type="text" placeholder="Nama Penjual" class="input input-bordered w-full rounded-xl bg-gray-50 focus:bg-white transition-colors" />
                            </div>
                        </div>

                        <!-- Checkbox Stok Pending (khusus admin) -->
                        <div class="form-control">
                            <label class="label cursor-pointer justify-start gap-4">
                                <input type="checkbox" wire:model="sudah_bayar" class="checkbox checkbox-primary" /> 
                                <div>
                                    <span class="label-text font-bold">Sudah Dibayar/Transfer Dulu?</span>
                                    <div class="text-[10px] text-gray-500 leading-tight">Centang jika uang sudah dicatat keluar (Stok Pending).</div>
                                </div>
                            </label>
                        </div>
                        @endif

                        <!-- Actions -->
                        <div class="pt-2">
                            <button type="submit" class="btn btn-primary w-full rounded-xl text-lg font-bold shadow-lg shadow-blue-200">
                                <span wire:loading.remove>Simpan Stok</span>
                                <span wire:loading class="loading loading-spinner loading-sm"></span>
                            </button>
                        </div>
                    </form>
                @else
                    <!-- FORM BORONGAN -->
                    <div class="space-y-4">
                        <!-- Sumber Beli (Global, khusus admin) -->
                        @if(auth()->user()?->isAdmin())
                        <div class="form-control w-full">
                            <label class="label py-1">
                                <span class="label-text font-medium text-gray-600">Sumber Beli (Borongan)</span>
                            </label>
                            <input wire:model="sumber_beli" type="text" placeholder="Contoh: Pak Budi" class="input input-bordered w-full rounded-xl bg-gray-50 focus:bg-white transition-colors @error('sumber_beli') input-error @enderror" />
                            @error('sumber_beli') <span class="text-error text-xs mt-1 ml-1">{{ $message }}</span> @enderror
                        </div>
                        @endif

                        <!-- List Item Sementara -->
                        @if(count($bulkItems) > 0)
                            <div class="bg-gray-50 rounded-xl p-3 border border-gray-200">
                                <h4 class="text-xs font-bold text-gray-500 uppercase mb-2">Daftar Barang</h4>
                                <div class="space-y-2 max-h-40 overflow-y-auto">
                                    @foreach($bulkItems as $index => $item)
                                        <div class="flex justify-between items-center bg-white p-2 rounded-lg border border-gray-100 shadow-sm">
                                            <div>
                                                <div class="font-bold text-sm">{{ $item['merk_model'] }}</div>
                                                <div class="text-xs text-gray-400">{{ $item['imei'] }}</div>
                                                @if(!empty($item['warna']) || !empty($item['keterangan_minus']))
                                                    <div class="text-[10px] text-gray-500 mt-0.5">
                                                        {{ $item['warna'] ?? '-' }} 
                                                        @if(!empty($item['keterangan_minus'])) | <span class="text-red-500">{{ $item['keterangan_minus'] }}</span> @endif
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="flex items-center gap-2">
                                                @if(auth()->user()?->isAdmin())
                                                <span class="text-sm font-semibold text-gray-600">Rp {{ number_format($item['harga_beli_awal'], 0, ',', '.') }}</span>
                                                @endif
                                                <button wire:click="removeBulkItem({{ $index }})" class="btn btn-xs btn-circle btn-ghost text-red-500">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                @if(auth()->user()?->isAdmin())
                                <div class="flex justify-between items-center mt-3 pt-2 border-t border-gray-200">
                                    <span class="text-sm font-bold text-gray-600">Total Borongan:</span>
                                    <span class="text-lg font-bold text-primary">Rp {{ number_format($total_borongan, 0, ',', '.') }}</span>
                                </div>
                                @endif
                            </div>
                        @endif

                        <!-- Form Tambah Item -->
                        <div class="bg-blue-50 p-3 rounded-xl border border-blue-100">
                            <h4 class="text-xs font-bold text-blue-800 uppercase mb-2">Input Barang</h4>
                            <div class="space-y-2">
                                <div class="relative">
                                    <input wire:model="imei" type="text" placeholder="IMEI" class="input input-sm input-bordered w-full pr-8" />
                                    <button type="button" onclick="startScan()" class="absolute right-1 top-1/2 -translate-y-1/2 btn btn-xs btn-ghost btn-square text-gray-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    </button>
                                </div>
                                @error('imei') <span class="text-error text-xs">{{ $message }}</span> @enderror
                                
                                <input wire:model="merk_model" type="text" placeholder="Merk & Model" class="input input-sm input-bordered w-full" />
                                @error('merk_model') <span class="text-error text-xs">{{ $message }}</span> @enderror

                                <div class="grid grid-cols-2 gap-2">
                                    <input wire:model="warna" type="text" placeholder="Warna" class="input input-sm input-bordered w-full" />
                                    <input wire:model="keterangan_minus" type="text" placeholder="Minus (Opsional)" class="input input-sm input-bordered w-full" />
                                </div>
                                
                                @if(auth()->user()?->isAdmin())
                                <div class="relative" x-data="{
                                    price: @entangle('harga_beli_awal'),
                                    format(val) { return val ? new Intl.NumberFormat('id-ID').format(val) : '' },
                                    input(e) {
                                        let raw = e.target.value.replace(/[^0-9]/g, '');
                                        this.price = raw;
                                        e.target.value = this.format(raw);
                                    }
                                }">
                                    <input type="text" :value="format(price)" @input="input" placeholder="Harga Beli Unit Ini" class="input input-sm input-bordered w-full pl-9" />
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none">Rp</span>
                                </div>
                                @error('harga_beli_awal') <span class="text-error text-xs">{{ $message }}</span> @enderror
                                @endif

                                <button wire:click="addBulkItem" class="btn btn-sm btn-secondary w-full text-white">
                                    + Tambah ke Daftar
                                </button>
                            </div>
                        </div>

                        <div class="pt-2">
                             <!-- Checkbox Stok Pending (khusus admin) -->
                             @if(auth()->user()?->isAdmin())
                             <div class="form-control mb-4">
                                <label class="label cursor-pointer justify-start gap-4">
                                    <input type="checkbox" wire:model="sudah_bayar" class="checkbox checkbox-primary" /> 
                                    <div>
                                        <span class="label-text font-bold">Sudah Dibayar/Transfer Dulu?</span>
                                        <div class="text-[10px] text-gray-500 leading-tight">Centang jika uang sudah dicatat keluar (Stok Pending).</div>
                                    </div>
                                </label>
                            </div>
                            @endif

                            <button wire:click="saveBulk" class="btn btn-primary w-full rounded-xl text-lg font-bold shadow-lg shadow-blue-200" {{ count($bulkItems) == 0 ? 'disabled' : '' }}>
                                <span wire:loading.remove>Simpan Semua ({{ count($bulkItems) }} Item)</span>
                                <span wire:loading class="loading loading-spinner loading-sm"></span>
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Backdrop click to close -->
        <form method="dialog" class="modal-backdrop bg-black/20 backdrop-blur-sm">
            <button>close</button>
        </form>
    </dialog>

    <!-- Script untuk handle event close dari Livewire -->
    <script>
        window.addEventListener('close-modal', event => {
            document.getElementById('modal_input_stok').close();
        })
    </script>

    <!-- Modal Scanner -->
    <dialog id="modal_scan" class="modal modal-bottom sm:modal-middle">
        <div class="modal-box relative w-full max-w-md mx-auto rounded-t-3xl rounded-b-none sm:rounded-2xl p-0 bg-white shadow-2xl">
            <div class="p-4">
                <h3 class="font-bold text-lg mb-4 text-center">Scan Barcode</h3>
                <div id="reader-container" class="w-full bg-black rounded-lg overflow-hidden">
                    <video id="reader-video"></video>
                </div>
                <div class="modal-action justify-center">
                    <button type="button" onclick="stopScan()" class="btn btn-ghost text-red-500">Batal</button>
                </div>
            </div>
        </div>
    </dialog>

    <style>
        #reader-container { width: 100%; height: 300px; position: relative; overflow: hidden; }
        #reader-video { width: 100%; height: 100%; object-fit: cover; }
    </style>
    <script>
        (function () {
            if (window.startScan) return; // Sudah pernah dideklarasikan (navigasi Livewire) — jangan redeclare

            let codeReader;
            let isScanning = false;

            window.startScan = function () {
                if (!navigator.mediaDevices && window.location.protocol !== 'https:' && window.location.hostname !== 'localhost') {
                    alert('Fitur kamera memerlukan koneksi aman (HTTPS) atau localhost.');
                    return;
                }

                const modal = document.getElementById('modal_scan');
                modal.showModal();
                isScanning = true;

                setTimeout(() => {
                    if (!codeReader) {
                        codeReader = new window.BrowserMultiFormatReader();
                    }

                    codeReader.decodeFromVideoDevice(null, 'reader-video', (result, err) => {
                        if (result && isScanning) {
                            console.log("Sukses Scan Code: " + result.getText());

                            // Isi data ke input Livewire
                            @this.set('imei', result.getText());

                            // Langsung tutup setelah sukses membaca 1x
                            stopScan();
                        }
                        if (err && !(err instanceof window.NotFoundException)) {
                            // Jangan alert NotFoundException (itu normal artinya frame tersebut tidak ada barcode)
                            // console.error(err);
                        }
                    }).catch((err) => {
                        console.error("Kesalahan inisialisasi kamera:", err);
                        alert("Kamera gagal diakses: " + err);
                        stopScan();
                    });
                }, 300);
            };

            window.stopScan = function () {
                isScanning = false;
                if (codeReader) {
                    try {
                        // Reset akan menghentikan stream kamera dan membersihkan listener
                        codeReader.reset();
                    } catch (e) {
                        console.error(e);
                    }
                }

                const modal = document.getElementById('modal_scan');
                if (modal) {
                    modal.close();
                }
            };
        })();
    </script>
</div>
