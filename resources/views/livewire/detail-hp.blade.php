<div>
    <dialog id="modal_detail_hp" class="modal modal-bottom sm:modal-middle" wire:ignore.self>
        <div class="modal-box relative w-full max-w-md mx-auto rounded-t-3xl rounded-b-none sm:rounded-2xl p-0 bg-white shadow-2xl">
            
            <!-- Handle Bar -->
            <div class="w-full flex justify-center pt-3 pb-1" onclick="modal_detail_hp.close()">
                <div class="w-12 h-1.5 bg-gray-300 rounded-full"></div>
            </div>

            @if($hp)
            <div class="p-6 pt-2">
                
                @if($isEditing)
                    <!-- FORM EDIT -->
                    <div class="space-y-4">
                        <h3 class="font-bold text-xl text-gray-800 mb-4">Edit Data HP</h3>
                        
                        <div class="form-control w-full">
                            <label class="label py-1"><span class="label-text font-medium">Merk & Model</span></label>
                            <input wire:model="edit_merk_model" type="text" class="input input-bordered w-full" />
                            @error('edit_merk_model') <span class="text-error text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="form-control w-full">
                                <label class="label py-1"><span class="label-text font-medium">Warna</span></label>
                                <input wire:model="edit_warna" type="text" class="input input-bordered w-full" />
                            </div>
                            <div class="form-control w-full">
                                <label class="label py-1"><span class="label-text font-medium">Minus</span></label>
                                <input wire:model="edit_minus" type="text" class="input input-bordered w-full" />
                            </div>
                        </div>

                        <div class="form-control w-full">
                            <label class="label py-1"><span class="label-text font-medium">Sumber Beli</span></label>
                            <input wire:model="edit_sumber_beli" type="text" class="input input-bordered w-full" />
                        </div>

                        <div class="form-control w-full">
                            <label class="label py-1"><span class="label-text font-medium">Harga Beli Awal</span></label>
                            <div class="relative" x-data="{
                                price: @entangle('edit_harga_beli_awal'),
                                format(val) { return val ? new Intl.NumberFormat('id-ID').format(val) : '' },
                                input(e) {
                                    let raw = e.target.value.replace(/[^0-9]/g, '');
                                    this.price = raw;
                                    e.target.value = this.format(raw);
                                }
                            }">
                                <input type="text" :value="format(price)" @input="input" class="input input-bordered w-full pl-10" />
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-semibold pointer-events-none">Rp</span>
                            </div>
                            @error('edit_harga_beli_awal') <span class="text-error text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex gap-2 pt-4">
                            <button wire:click="toggleEdit" class="btn btn-error text-white flex-1">Batal</button>
                            <button wire:click="updateHp" class="btn btn-primary flex-1">Simpan Perubahan</button>
                        </div>
                    </div>
                @else
                    <!-- TAMPILAN DETAIL -->
                    <!-- Dummy focus element to prevent dropdown auto-open -->
                    <button class="opacity-0 absolute h-0 w-0 overflow-hidden"></button>
                    
                    <!-- Header Info -->
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="font-bold text-2xl text-gray-800">{{ $hp->merk_model }}</h3>
                            
                            <!-- Warna & Minus -->
                            <div class="mt-2 flex flex-wrap gap-2">
                                @if($hp->warna)
                                    <span class="badge badge-ghost text-xs">{{ $hp->warna }}</span>
                                @endif
                                @if($hp->keterangan_minus)
                                    <span class="badge badge-error badge-outline text-xs">Minus: {{ $hp->keterangan_minus }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="flex flex-col items-end gap-2">
                            <div class="badge {{ $hp->status == 'READY' ? 'badge-success' : 'badge-warning' }} text-white">
                                {{ $hp->status }}
                            </div>
                            
                            <!-- Menu Actions -->
                            <div class="dropdown dropdown-end">
                                <div tabindex="0" role="button" class="btn btn-xs btn-circle btn-ghost">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 12.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 18.75a.75.75 0 110-1.5.75.75 0 010 1.5z" />
                                    </svg>
                                </div>
                                <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-40 border border-gray-100">
                                    <li><a wire:click="toggleEdit" class="text-gray-600">Edit Data</a></li>
                                    @if($hp->status == 'SERVICE')
                                        <li><a wire:click="markAsReady" class="text-success">Selesai Service</a></li>
                                    @endif
                                    <li><a onclick="confirm_delete_hp.showModal()" class="text-error">Hapus Unit</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Financial Card -->
                    @if(!$hp->modalBelumDiisi())
                    <div class="card bg-blue-50 border border-blue-100 mb-6">
                        <div class="card-body p-4">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-xs text-gray-500">Harga Beli Awal</span>
                                <span class="font-semibold text-gray-700">Rp {{ number_format($hp->harga_beli_awal, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-xs text-gray-500">Sumber Beli</span>
                                <span class="font-semibold text-gray-700">{{ $hp->sumber_beli ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-xs text-gray-500">Total Biaya Service</span>
                                <span class="font-semibold text-red-500">+ Rp {{ number_format($hp->total_modal - $hp->harga_beli_awal, 0, ',', '.') }}</span>
                            </div>
                            <div class="divider my-1"></div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-bold text-blue-800">Total Modal Saat Ini</span>
                                <span class="text-lg font-bold text-blue-800">Rp {{ number_format($hp->total_modal, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                    @else
                    @if(auth()->user()?->isAdmin())
                    <!-- Modal belum diisi: tombol langsung isi tanpa lewat menu Edit -->
                    <div class="card bg-amber-50 border border-amber-200 mb-6">
                        <div class="card-body p-4">
                            <div class="flex items-center gap-3">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8 text-amber-500 shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <div class="flex-1">
                                    <div class="font-bold text-amber-800 text-sm">Harga modal belum diisi</div>
                                    @if($hp->pelacak)
                                    <div class="text-[10px] text-amber-500 mt-0.5">Diinput oleh: {{ $hp->pelacak->name }}</div>
                                    @endif
                                </div>
                            </div>
                            <button wire:click="toggleEdit" class="btn btn-sm btn-warning text-white w-full mt-3 rounded-xl font-bold">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="inline-block w-4 h-4 mr-1"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" /></svg>
                                Isi Harga Modal
                            </button>
                        </div>
                    </div>
                    @endif
                    @endif

                <!-- Service History Section -->
                <div class="mb-6">
                    <div class="flex justify-between items-center mb-3">
                        <h4 class="font-bold text-gray-700">Riwayat Service</h4>
                        <!-- Ganti $toggle dengan wire:click biasa ke method toggleServiceForm -->
                        <button wire:click="toggleServiceForm" class="btn btn-xs btn-outline btn-primary">
                            {{ $showServiceForm ? 'Batal' : '+ Tambah Service' }}
                        </button>
                    </div>

                    <!-- Form Tambah Service (Toggle) -->
                    @if($showServiceForm)
                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 mb-4 animate-fade-in-down">
                        <form wire:submit="saveService">
                            <div class="form-control w-full mb-2">
                                <input wire:model="deskripsi_service" type="text" placeholder="Keterangan (Misal: Ganti LCD)" class="input input-sm input-bordered w-full" />
                                @error('deskripsi_service') <span class="text-error text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-control w-full mb-3">
                                <div class="relative" x-data="{
                                    price: @entangle('biaya_service'),
                                    format(val) { return val ? new Intl.NumberFormat('id-ID').format(val) : '' },
                                    input(e) {
                                        let raw = e.target.value.replace(/[^0-9]/g, '');
                                        this.price = raw;
                                        e.target.value = this.format(raw);
                                    }
                                }">
                                    <span class="absolute left-2 top-1/2 -translate-y-1/2 text-gray-400 text-xs">Rp</span>
                                    <input type="text" :value="format(price)" @input="input" placeholder="Biaya" class="input input-sm input-bordered w-full pl-6" />
                                </div>
                                @error('biaya_service') <span class="text-error text-xs">{{ $message }}</span> @enderror
                            </div>
                            <button type="submit" class="btn btn-sm btn-primary w-full">Simpan Biaya</button>
                        </form>
                    </div>
                    @endif

                    <!-- List Service -->
                    <div class="space-y-2">
                        @forelse($hp->services as $service)
                            <div class="flex justify-between items-center bg-white p-3 rounded-lg border border-gray-100 shadow-sm">
                                <div>
                                    <p class="font-semibold text-sm text-gray-800">{{ $service->deskripsi }}</p>
                                    <p class="text-xs text-gray-400">{{ $service->created_at->format('d M Y') }}</p>
                                </div>
                                <span class="font-bold text-red-500 text-sm">- Rp {{ number_format($service->biaya, 0, ',', '.') }}</span>
                            </div>
                        @empty
                            <p class="text-center text-gray-400 text-sm py-4 italic">Belum ada riwayat service.</p>
                        @endforelse
                    </div>
                </div>
                @endif

            </div>
            @else
            <div class="flex-1 flex items-center justify-center">
                <span class="loading loading-spinner loading-lg text-primary"></span>
            </div>
            @endif
        </div>

        <form method="dialog" class="modal-backdrop bg-black/20 backdrop-blur-sm">
            <button>close</button>
        </form>
    </dialog>

    <!-- Modal Konfirmasi Hapus -->
    <dialog id="confirm_delete_hp" class="modal modal-bottom sm:modal-middle">
        <div class="modal-box">
            <h3 class="font-bold text-lg text-error">Hapus Data HP?</h3>
            <p class="py-4">Apakah Anda yakin ingin menghapus data HP ini? Tindakan ini tidak dapat dibatalkan.</p>
            <div class="modal-action">
                <form method="dialog">
                    <button class="btn btn-ghost">Batal</button>
                </form>
                <button wire:click="deleteHp" class="btn btn-error text-white" onclick="confirm_delete_hp.close()">Ya, Hapus</button>
            </div>
        </div>
    </dialog>

    <script>
        window.addEventListener('show-modal-detail', event => {
            document.getElementById('modal_detail_hp').showModal();
        });
        
        window.addEventListener('close-modal-detail', event => {
            document.getElementById('modal_detail_hp').close();
        })
    </script>
</div>
