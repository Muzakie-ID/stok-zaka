<x-layouts.auth>
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
    <h1 class="text-xl font-extrabold text-slate-800">Masuk</h1>
    <p class="text-sm text-slate-500 mt-1 mb-6">Catatan stok & penjualan HP.</p>

    @if (session('status'))
        <div class="mb-4 rounded-xl bg-emerald-50 text-emerald-700 text-sm px-4 py-3">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf
        <div>
            <label for="email" class="block text-sm font-semibold text-slate-700 mb-1">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                   autocomplete="username" inputmode="email"
                   class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-emerald-500 focus:ring-emerald-500 outline-none @error('email') border-red-400 @enderror">
            @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-semibold text-slate-700 mb-1">Kata sandi</label>
            <input id="password" name="password" type="password" required autocomplete="current-password"
                   class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-emerald-500 focus:ring-emerald-500 outline-none @error('password') border-red-400 @enderror">
            @error('password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <label class="flex items-center gap-2 text-sm text-slate-600">
            <input type="checkbox" name="remember" value="1" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
            Ingat saya
        </label>

        <button type="submit" class="w-full rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-3 transition">Masuk</button>
    </form>

    <p class="text-xs text-slate-400 mt-6 text-center">
        Belum punya akun? <a href="{{ route('register') }}" class="text-emerald-600 font-semibold">Daftar</a>
    </p>
</div>
</x-layouts.auth>
