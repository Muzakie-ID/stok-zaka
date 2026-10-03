@php($riwayat = \App\Models\SalaryTransaction::with('admin')->where('user_id', $userId)->latest()->take(10)->get())
@if ($riwayat->isEmpty())
    <p class="text-xs text-gray-400 text-center py-2">Belum ada riwayat transaksi.</p>
@else
    <div class="space-y-2">
        @foreach ($riwayat as $r)
            <div class="flex justify-between items-center bg-gray-50 rounded-xl px-3 py-2" wire:key="r-{{ $r->id }}">
                <div class="min-w-0">
                    <div class="text-xs font-semibold {{ $r->type === 'earn' ? 'text-emerald-600' : 'text-red-500' }}">
                        {{ $r->type === 'earn' ? '+' : '-' }} Rp {{ number_format($r->amount, 0, ',', '.') }}
                    </div>
                    <div class="text-[11px] text-gray-400 truncate">{{ $r->description }}</div>
                </div>
                <div class="text-right shrink-0 ml-2">
                    <div class="text-[10px] text-gray-400">{{ $r->created_at->translatedFormat('d M Y') }}</div>
                    <div class="text-[10px] text-gray-300">oleh {{ $r->admin->name }}</div>
                </div>
            </div>
        @endforeach
    </div>
@endif
