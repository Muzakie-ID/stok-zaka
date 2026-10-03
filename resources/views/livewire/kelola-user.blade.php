<div>
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-lg font-extrabold text-slate-800">Kelola User</h1>
            <p class="text-xs text-slate-500">{{ $totalAdmin }} admin &middot; {{ $totalKaryawan }} karyawan</p>
        </div>
        <button wire:click="openTambah"
                class="rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold px-4 py-2.5 transition">
            + Tambah
        </button>
    </div>

    @if ($flash)
        <div class="mb-3 rounded-xl bg-emerald-50 text-emerald-700 text-sm px-4 py-3">{{ $flash }}</div>
    @endif

    {{-- Form tambah / edit --}}
    @if ($formMode !== '')
        <div class="fixed inset-0 z-50 bg-slate-900/40 flex items-end sm:items-center justify-center p-0 sm:p-4"
             x-data="{ open: true }" x-show="open" x-cloak>
            <div class="bg-white w-full sm:max-w-md rounded-t-2xl sm:rounded-2xl p-5 max-h-[90vh] overflow-y-auto"
                 @click.outside="$wire.closeForm()">
                <h2 class="font-extrabold text-slate-800 mb-4">
                    {{ $formMode === 'edit' ? 'Edit akun' : 'Tambah akun' }}
                </h2>

                <form wire:submit="simpan" class="space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Nama</label>
                        <input type="text" wire:model="name" autocomplete="off"
                               class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-emerald-500 @error('name') border-red-400 @enderror">
                        @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Email</label>
                        <input type="email" wire:model="email" inputmode="email" autocomplete="off"
                               class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-emerald-500 @error('email') border-red-400 @enderror">
                        @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Role</label>
                        <select wire:model.live="role"
                                class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-emerald-500 @error('role') border-red-400 @enderror">
                            <option value="karyawan">Karyawan (stok & penjualan)</option>
                            <option value="admin">Admin (semua halaman)</option>
                        </select>
                        @error('role') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">
                            Kata sandi {{ $formMode === 'edit' ? '(kosongkan kalau tidak diubah)' : '' }}
                        </label>
                        <input type="password" wire:model="password" autocomplete="new-password"
                               class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-emerald-500 @error('password') border-red-400 @enderror">
                        @error('password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Ulangi kata sandi</label>
                        <input type="password" wire:model="password_confirmation" autocomplete="new-password"
                               class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-emerald-500">
                    </div>

                    <div class="flex gap-2 pt-2">
                        <button type="submit" class="flex-1 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold py-3 transition">
                            Simpan
                        </button>
                        <button type="button" wire:click="closeForm"
                                class="rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold px-5 py-3 transition">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Cari --}}
    <input type="search" wire:model.live.debounce.300ms="search" placeholder="Cari nama atau email..."
           class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm mb-3 outline-none focus:border-emerald-500 bg-white">

    {{-- Daftar --}}
    <div class="bg-white rounded-2xl border border-slate-100 divide-y divide-slate-100 overflow-hidden">
        @forelse ($users as $u)
            <div class="flex items-center gap-3 p-3">
                <div class="w-9 h-9 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center text-sm font-bold shrink-0">
                    {{ strtoupper(substr($u->name, 0, 1)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-slate-800 truncate">{{ $u->name }}</p>
                    <p class="text-xs text-slate-400 truncate">{{ $u->email }}</p>
                </div>
                <span class="text-[10px] font-bold px-2 py-1 rounded-full shrink-0 {{ $u->isAdmin() ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                    {{ $u->roleLabel() }}
                </span>
                <div class="flex gap-1.5 shrink-0">
                    <button wire:click="openEdit({{ $u->id }})"
                            class="rounded-xl bg-slate-100 hover:bg-slate-200 active:scale-95 text-slate-600 text-xs font-bold px-3 py-2 transition">
                        Ubah
                    </button>
                    <button wire:click="hapus({{ $u->id }})" wire:confirm="Hapus akun {{ $u->name }}?"
                            class="rounded-xl bg-red-50 hover:bg-red-100 active:scale-95 text-red-600 text-xs font-bold px-3 py-2 transition">
                        Hapus
                    </button>
                </div>
            </div>
        @empty
            <p class="text-sm text-slate-400 text-center py-8">Belum ada akun.</p>
        @endforelse
    </div>

    <div class="mt-3">{{ $users->links() }}</div>
</div>