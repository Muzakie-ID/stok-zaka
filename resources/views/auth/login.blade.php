<x-layouts.auth>
    <x-auth.card title="Masuk" subtitle="Masuk untuk kelola stok dan penjualan.">
        @if (session('status'))
            <div class="rounded-xl bg-emerald-50 text-emerald-700 text-sm px-4 py-3 mb-4">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-4 mt-5">
            @csrf

            <x-auth.field name="email" label="Email" type="email" autocomplete="username" inputmode="email" autofocus />

            <x-auth.field name="password" label="Kata sandi" type="password" autocomplete="current-password" />

            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" name="remember" value="1"
                       class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                Ingat saya
            </label>

            <x-auth.button>Masuk</x-auth.button>
        </form>

        <p class="text-xs text-slate-400 mt-6 text-center">
            Belum punya akun?
            <a href="{{ route('register') }}" class="text-emerald-600 font-semibold">Daftar</a>
        </p>
    </x-auth.card>
</x-layouts.auth>