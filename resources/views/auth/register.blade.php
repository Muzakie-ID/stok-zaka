<x-layouts.auth>
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
    <h1 class="text-xl font-extrabold text-slate-800">Daftar karyawan</h1>
    <p class="text-sm text-slate-500 mt-1 mb-6">Akun baru otomatis jadi role karyawan.</p>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf
        <div>
            <label for="name" class="block text-sm font-semibold text-slate-700 mb-1">Nama</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus
                   class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-emerald-500 focus:ring-emerald-500 outline-none @error('name') border-red-400 @enderror">
            @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="email" class="block text-sm font-semibold text-slate-700 mb-1">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required inputmode="email"
                   class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-emerald-500 focus:ring-emerald-500 outline-none @error('email') border-red-400 @enderror">
            @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-semibold text-slate-700 mb-1">Kata sandi</label>
            <input id="password" name="password" type="password" required autocomplete="new-password"
                   class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-emerald-500 focus:ring-emerald-500 outline-none @error('password') border-red-400 @enderror">
            @error('password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-semibold text-slate-700 mb-1">Ulangi kata sandi</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                   class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-emerald-500 focus:ring-emerald-500 outline-none">
        </div>

        <button type="submit" class="w-full rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-3 transition">Daftar</button>
    </form>

    <p class="text-xs text-slate-400 mt-6 text-center">
        Sudah punya akun? <a href="{{ route('login') }}" class="text-emerald-600 font-semibold">Masuk</a>
    </p>
</div>
</x-layouts.auth>
