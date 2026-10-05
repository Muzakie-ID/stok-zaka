<x-layouts.auth>
    <x-auth.card title="Profil" subtitle="{{ $user->name }} · {{ $user->roleLabel() }}">
        @if (session('status'))
            <div class="rounded-xl bg-emerald-50 text-emerald-700 text-sm px-4 py-3 mt-5">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('profile.edit') }}" class="space-y-4 mt-5">
            @csrf

            <div class="text-sm text-slate-600 bg-slate-50 rounded-xl px-4 py-3">
                Email: <span class="font-semibold text-slate-800">{{ $user->email }}</span>
            </div>

            <x-auth.field name="current_password" label="Kata sandi sekarang" type="password" autocomplete="current-password" />

            <x-auth.field name="password" label="Kata sandi baru" type="password" autocomplete="new-password" />

            <x-auth.field name="password_confirmation" label="Ulangi kata sandi baru" type="password" autocomplete="new-password" />

            <x-auth.button>Simpan</x-auth.button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="mt-3">
            @csrf @method('DELETE')
            <x-auth.button variant="secondary">
                Keluar
            </x-auth.button>
        </form>
    </x-auth.card>
</x-layouts.auth>