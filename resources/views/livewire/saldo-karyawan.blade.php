<div class="pb-24"
     x-data="{}"
     @tutup-modal-upah="document.querySelectorAll('dialog[open]').forEach(d => d.close())">
    <!-- Header -->
    <div class="bg-white p-4 sticky top-14 z-30 shadow-sm border-b border-gray-200">
        <div class="flex justify-between items-center">
            <h1 class="text-xl font-bold text-gray-800">Saldo Karyawan</h1>
        </div>
        <div class="relative mt-3">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari karyawan..."
                   class="input input-bordered w-full pl-10 rounded-xl shadow-sm" />
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 absolute left-3 top-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
        </div>
    </div>

    <div class="p-4 space-y-3">
        @if (session('message') || $flash)
            <div class="rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-700 text-sm px-4 py-3">
                {{ session('message') ?: $flash }}
            </div>
        @endif

        <!-- Total -->
        <div class="card bg-gradient-to-br from-emerald-600 to-emerald-800 text-white shadow-lg">
            <div class="card-body p-5">
                <h2 class="text-sm font-medium opacity-80">Total Saldo Berjalan</h2>
                <p class="text-3xl font-bold">Rp {{ number_format($totalSaldo, 0, ',', '.') }}</p>
                <p class="text-xs opacity-70 mt-1">Ditambahkan ke {{ $karyawans->count() }} karyawan</p>
            </div>
        </div>

        <!-- Daftar karyawan -->
        @forelse ($karyawans as $k)
            <div class="card bg-white shadow-sm border border-gray-100" x-data="{ open: false }">
                <div class="card-body p-4">
                    <div class="flex justify-between items-center gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-full bg-emerald-600 text-white grid place-items-center text-xs font-bold shrink-0">
                                {{ strtoupper(substr($k->name, 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <h3 class="font-bold text-gray-800 truncate">{{ $k->name }}</h3>
                                <p class="text-xs text-gray-400 truncate">{{ $k->email }}</p>
                            </div>
                        </div>
                        <div class="text-right shrink-0">
                            <div class="text-sm font-bold {{ $k->saldo > 0 ? 'text-emerald-600' : 'text-gray-400' }}">
                                Rp {{ number_format($k->saldo, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-2 mt-3">
                        <button onclick="modal_upah_{{ $k->id }}.showModal()" @click="$wire.openForm({{ $k->id }}, 'earn')"
                                class="btn btn-sm flex-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl border-none">
                            + Kasih Upah
                        </button>
                        <button onclick="modal_upah_{{ $k->id }}.showModal()" @click="$wire.openForm({{ $k->id }}, 'paid')"
                                class="btn btn-sm flex-1 btn-outline rounded-xl {{ $k->saldo <= 0 ? 'btn-disabled' : '' }}">
                            Cairkan
                        </button>
                        <button class="btn btn-sm btn-ghost btn-square rounded-xl" @click="open = !open" title="Riwayat">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </button>
                    </div>

                    <!-- Riwayat -->
                    <div x-show="open" x-cloak class="mt-3 border-t border-gray-100 pt-3">
                        <div wire:key="riwayat-{{ $k->id }}-{{ \App\Models\SalaryTransaction::where('user_id', $k->id)->max('updated_at') }}">
                            @include('livewire.partials.saldo-riwayat', ['userId' => $k->id])
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal kasih upah / cairkan -->
            <dialog id="modal_upah_{{ $k->id }}" class="modal modal-bottom sm:modal-middle" wire:ignore.self>
                <div class="modal-box relative w-full max-w-md mx-auto rounded-t-3xl rounded-b-none sm:rounded-2xl p-0 bg-white shadow-2xl">
                    <div class="w-full flex justify-center pt-3 pb-1" onclick="modal_upah_{{ $k->id }}.close()">
                        <div class="w-12 h-1.5 bg-gray-300 rounded-full"></div>
                    </div>
                    <div class="p-6 pt-2">
                        <h3 class="font-bold text-xl text-gray-800 mb-1">
                            {{ $type === 'paid' ? 'Cairkan Upah' : 'Kasih Upah' }}
                        </h3>
                        <p class="text-sm text-gray-500 mb-4">Untuk: <span class="font-semibold text-gray-700">{{ $userName }}</span></p>

                        <form wire:submit="simpan" class="space-y-4">
                            <!-- Pilihan jenis -->
                            <div class="tabs tabs-boxed bg-gray-100 p-1">
                                <a wire:click="$set('type', 'earn')"
                                   class="tab w-1/2 {{ $type === 'earn' ? 'tab-active bg-white shadow-sm text-emerald-600 font-bold' : 'text-gray-500' }}">Kasih Upah</a>
                                <a wire:click="$set('type', 'paid')"
                                   class="tab w-1/2 {{ $type === 'paid' ? 'tab-active bg-white shadow-sm text-red-500 font-bold' : 'text-gray-500' }}">Cairkan</a>
                            </div>

                            <div class="form-control w-full">
                                <label class="label py-1"><span class="label-text font-medium text-gray-600">Nominal</span></label>
                                <div class="relative" x-data="{
                                    format(v) { const d = v.replace(/[^0-9]/g, ''); return d ? new Intl.NumberFormat('id-ID').format(parseInt(d)) : ''; }
                                }">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm">Rp</span>
                                    <input type="text" inputmode="numeric" placeholder="0"
                                           wire:model="amount"
                                           x-on:input="$el.value = format($el.value)"
                                           class="input input-bordered w-full pl-11 rounded-xl bg-gray-50 focus:bg-white transition-colors" />
                                </div>
                                @error('amount') <span class="text-error text-xs mt-1 ml-1">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-control w-full">
                                <label class="label py-1"><span class="label-text font-medium text-gray-600">Keterangan (opsional)</span></label>
                                <input type="text" wire:model="description" placeholder="Contoh: Upah jual 2 unit minggu ini"
                                       class="input input-bordered w-full rounded-xl bg-gray-50 focus:bg-white transition-colors" />
                            </div>

                            <button type="submit" class="w-full rounded-xl py-3 font-bold text-white transition-all active:scale-[0.98] {{ $type === 'earn' ? 'bg-emerald-600 hover:bg-emerald-700 shadow-[0_8px_20px_rgba(16,185,129,0.3)]' : 'bg-red-500 hover:bg-red-600 shadow-[0_8px_20px_rgba(239,68,68,0.3)]' }}">
                                {{ $type === 'earn' ? 'Kasih Upah' : 'Cairkan' }}
                            </button>
                        </form>
                    </div>
                </div>
            </dialog>
        @empty
            <div class="text-center text-gray-400 py-16">
                <p class="text-sm">Belum ada karyawan terdaftar.</p>
            </div>
        @endforelse
    </div>
</div>
