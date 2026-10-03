<x-layouts.auth>
    <x-auth.card title="Daftar karyawan" subtitle="Akun baru otomatis jadi role karyawan.">
        <form method="POST" action="{{ route('register') }}" class="space-y-4 mt-5">
            @csrf

            <x-auth.field name="name" label="Nama" autocomplete="name" autofocus />

            <x-auth.field name="email" label="Email" type="email" autocomplete="username" inputmode="email" />

            <x-auth.field name="password" label="Kata sandi" type="password" autocomplete="new-password" />

            <x-auth.field name="password_confirmation" label="Ulangi kata sandi" type="password" autocomplete="new-password" />

            <x-auth.button>Daftar</x-auth.button>
        </form>

        <p class="text-xs text-slate-400 mt-6 text-center">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="text-emerald-600 font-semibold">Masuk</a>
        </p>
    </x-auth.card>
</x-layouts.auth>