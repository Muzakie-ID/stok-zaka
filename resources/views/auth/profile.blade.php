<x-layouts.auth>
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
    <h1 class="text-xl font-extrabold text-slate-800">Profil</h1>
    <p class="text-sm text-slate-500 mt-1 mb-6">{{ $user->name }} &middot; {{ $user->roleLabel() }}</p>

    @if (session('status'))
        <div class="mb-4 rounded-xl bg-emerald-50 text-emerald-700 text-sm px-4 py-3">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('profile.edit') }}" class="space-y-4">
        @csrf
        <div class="text-sm text-slate-600 bg-slate-50 rounded-xl px-4 py-3">
            Email: <span class="font-semibold text-slate-800">{{ $user->email }}</span>
        </div>

        <div>
            <label for="current_password" class="block text-sm font-semibold text-slate-700 mb-1">Kata sandi sekarang</label>
            <input id="current_password" name="current_password" type="password" required autocomplete="current-password"
                   class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-emerald-500 focus:ring-emerald-500 outline-none @error('current_password') border-red-400 @enderror">
            @error('current_password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-semibold text-slate-700 mb-1">Kata sandi baru</label>
            <input id="password" name="password" type="password" required autocomplete="new-password"
                   class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-emerald-500 focus:ring-emerald-500 outline-none @error('password') border-red-400 @enderror">
            @error('password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-semibold text-slate-700 mb-1">Ulangi kata sandi baru</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                   class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-emerald-500 focus:ring-emerald-500 outline-none">
        </div>

        <button type="submit" class="w-full rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-3 transition">Simpan</button>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="mt-3">
        @csrf @method('DELETE')
        <button type="submit" class="w-full rounded-xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 font-semibold py-3 transition">Keluar</button>
    </form>
</div>
</x-layouts.auth>
